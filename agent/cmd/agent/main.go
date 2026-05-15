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

type PolicyPayload struct {
	PolicyName string        `json:"policy_name"`
	Rules      []interface{} `json:"rules"`
}

type IncomingCommand struct {
	Command string        `json:"command"`
	Payload PolicyPayload `json:"payload"`
}

func main() {
	log.Println("--- Agent Firewall Central (Policy-Based) ---")

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
	key, err := rsa.GenerateKey(rand.Reader, 2048)
	if err != nil { return err }
	keyPEM := pem.EncodeToMemory(&pem.Block{Type: "RSA PRIVATE KEY", Bytes: x509.MarshalPKCS1PrivateKey(key)})
	os.MkdirAll("certs", 0755)
	os.WriteFile("certs/agent.key", keyPEM, 0600)

	subj := pkix.Name{CommonName: "agent-new"}
	template := x509.CertificateRequest{Subject: subj}
	csrBytes, _ := x509.CreateCertificateRequest(rand.Reader, &template, key)
	csrPEM := pem.EncodeToMemory(&pem.Block{Type: "CERTIFICATE REQUEST", Bytes: csrBytes})

	hostname, _ := os.Hostname()
	payload, _ := json.Marshal(map[string]string{
		"token": token, "csr": string(csrPEM), "hostname": hostname, "os": "linux",
	})

	resp, err := http.Post("http://localhost:8080/api/v1/agent/bootstrap", "application/json", bytes.NewBuffer(payload))
	if err != nil { return err }
	defer resp.Body.Close()

	var result map[string]string
	json.NewDecoder(resp.Body).Decode(&result)
	if cert, ok := result["certificate"]; ok && cert != "" {
		os.WriteFile("certs/agent.crt", []byte(cert), 0644)
		os.WriteFile("certs/ca.crt", []byte(result["ca_cert"]), 0644)
		log.Println("Bootstrap réussi.")
	}
	return nil
}

func runAgent() {
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt)

	u := url.URL{Scheme: "wss", Host: "localhost:8081", Path: "/"}
	cert, err := tls.LoadX509KeyPair("certs/agent.crt", "certs/agent.key")
	if err != nil {
		log.Printf("Certificats mTLS introuvables.")
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
		log.Printf("Erreur connexion : %v", err)
		return
	}
	defer c.Close()

	log.Println("Agent connecté. Attente de politiques...")

	hostname, _ := os.Hostname()
	c.WriteMessage(websocket.TextMessage, []byte(fmt.Sprintf(`{"agent_id":"%s","type":"IDENT"}`, hostname)))

	go func() {
		for {
			_, message, err := c.ReadMessage()
			if err != nil { return }

			var cmd IncomingCommand
			if err := json.Unmarshal(message, &cmd); err == nil && cmd.Command == "APPLY_POLICY" {
				log.Printf("POLITIQUE REÇUE : %s (%d règles)", cmd.Payload.PolicyName, len(cmd.Payload.Rules))
				// MVP: Simuler application atomique
				c.WriteMessage(websocket.TextMessage, []byte(`{"status":"success","message":"Policy applied atomically"}`))
			}
		}
	}()

	for {
		select {
		case <-interrupt:
			return
		case <-time.After(30 * time.Second):
			c.WriteMessage(websocket.TextMessage, []byte(`{"type":"HEARTBEAT"}`))
		}
	}
}
