package main

import (
	"bytes"
	"crypto/rand"
	"crypto/rsa"
	"crypto/tls"
	"crypto/x509"
	"crypto/x509/pkix"
	"encoding/json"
	"encoding/pem"
	"fmt"
	"log"
	"net/http"
	"net/url"
	"os"
	"os/signal"
	"time"

	"github.com/gorilla/websocket"
)

func main() {
	log.Println("--- Agent Firewall Central (Automated Onboarding) ---")

	token := os.Getenv("AGENT_TOKEN")
	if token != "" {
		if err := bootstrap(token); err != nil {
			log.Fatalf("Bootstrap impossible : %v", err)
		}
	}

	runAgent()
}

func bootstrap(token string) error {
	log.Println("Démarrage du processus de bootstrap...")

	// 1. Générer une clé RSA
	key, err := rsa.GenerateKey(rand.Reader, 2048)
	if err != nil {
		return fmt.Errorf("erreur génération clé : %w", err)
	}

	keyPEM := pem.EncodeToMemory(&pem.Block{Type: "RSA PRIVATE KEY", Bytes: x509.MarshalPKCS1PrivateKey(key)})
	os.MkdirAll("certs", 0755)
	if err := os.WriteFile("certs/agent.key", keyPEM, 0600); err != nil {
		return fmt.Errorf("erreur sauvegarde clé : %w", err)
	}

	// 2. Créer un CSR
	subj := pkix.Name{CommonName: "agent-new"}
	template := x509.CertificateRequest{Subject: subj}
	csrBytes, err := x509.CreateCertificateRequest(rand.Reader, &template, key)
	if err != nil {
		return fmt.Errorf("erreur création CSR : %w", err)
	}
	csrPEM := pem.EncodeToMemory(&pem.Block{Type: "CERTIFICATE REQUEST", Bytes: csrBytes})

	// 3. Appeler l'API Bootstrap (HTTPS fortement recommandé en production)
	hostname, _ := os.Hostname()
	payload, _ := json.Marshal(map[string]string{
		"token":    token,
		"csr":      string(csrPEM),
		"hostname": hostname,
		"os":       "linux",
	})

	// Pour le MVP, on utilise localhost:8080 (HTTPS via proxy recommandé)
	apiURL := "http://localhost:8080/api/v1/agent/bootstrap"
	resp, err := http.Post(apiURL, "application/json", bytes.NewBuffer(payload))
	if err != nil {
		return fmt.Errorf("erreur API bootstrap : %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("API bootstrap a retourné le statut %d", resp.StatusCode)
	}

	var result map[string]string
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return fmt.Errorf("erreur décodage réponse : %w", err)
	}

	if cert, ok := result["certificate"]; ok && cert != "" {
		os.WriteFile("certs/agent.crt", []byte(cert), 0644)
		os.WriteFile("certs/ca.crt", []byte(result["ca_cert"]), 0644)
		log.Println("Bootstrap réussi. Identifiants mTLS installés.")
	} else {
		return fmt.Errorf("certificat manquant dans la réponse")
	}

	return nil
}

func runAgent() {
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt)

	u := url.URL{Scheme: "wss", Host: "localhost:8081", Path: "/"}
	log.Printf("Connexion mTLS à %s...", u.String())

	cert, err := tls.LoadX509KeyPair("certs/agent.crt", "certs/agent.key")
	if err != nil {
		log.Printf("Certificats mTLS introuvables. L'agent ne peut pas démarrer.")
		return
	}

	caCert, _ := os.ReadFile("certs/ca.crt")
	caCertPool := x509.NewCertPool()
	caCertPool.AppendCertsFromPEM(caCert)

	tlsConfig := &tls.Config{
		Certificates: []tls.Certificate{cert},
		RootCAs:      caCertPool,
		ServerName:   "localhost",
	}

	dialer := websocket.Dialer{TLSClientConfig: tlsConfig}
	c, _, err := dialer.Dial(u.String(), nil)
	if err != nil {
		log.Printf("Erreur connexion WebSocket : %v", err)
		return
	}
	defer c.Close()

	log.Println("Agent connecté via tunnel mTLS.")

	// Message d'identification
	c.WriteMessage(websocket.TextMessage, []byte(`{"agent_id":"agent-001","type":"IDENT"}`))

	for {
		select {
		case <-interrupt:
			log.Println("Fermeture...")
			return
		case <-time.After(30 * time.Second):
			c.WriteMessage(websocket.TextMessage, []byte(`{"type":"HEARTBEAT"}`))
		}
	}
}
