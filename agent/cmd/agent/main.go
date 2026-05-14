package main

import (
	"encoding/json"
	"log"
	"os"
	"os/signal"
	"time"

	"github.com/gorilla/websocket"
)

type CommandHeader struct {
	MsgID     string `json:"msg_id"`
	Timestamp string `json:"timestamp"`
	Command   string `json:"command"`
}

type CommandMessage struct {
	Header  CommandHeader `json:"header"`
	Payload interface{}   `json:"payload"`
}

func main() {
	log.Println("--- Agent Firewall Central MVP ---")

	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt)

	// URL du serveur WebSocket (port 8081 défini dans le backend)
	u := "ws://localhost:8081"
	log.Printf("Tentative de connexion à %s...", u)

	c, _, err := websocket.DefaultDialer.Dial(u, nil)
	if err != nil {
		log.Fatal("Erreur de connexion :", err)
	}
	defer c.Close()

	log.Println("Connecté au serveur central.")

	// Simulation d'envoi de registration
	reg := CommandMessage{
		Header: CommandHeader{
			MsgID:     "init-001",
			Timestamp: time.Now().Format(time.RFC3339),
			Command:   "REGISTER",
		},
		Payload: map[string]string{
			"hostname": "srv-mvp-01",
			"os":       "linux",
		},
	}

	msg, _ := json.Marshal(reg)
	err = c.WriteMessage(websocket.TextMessage, msg)
	if err != nil {
		log.Println("Erreur lors de l'enregistrement :", err)
	}

	ticker := time.NewTicker(time.Second * 30)
	defer ticker.Stop()

	for {
		select {
		case <-ticker.C:
			log.Println("Envoi Heartbeat...")
			err := c.WriteMessage(websocket.TextMessage, []byte(`{"command":"HEARTBEAT"}`))
			if err != nil {
				log.Println("Erreur Heartbeat :", err)
				return
			}
		case <-interrupt:
			log.Println("Arrêt de l'agent.")
			return
		}
	}
}
