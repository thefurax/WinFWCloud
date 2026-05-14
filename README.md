# Projet Firewall Central - MVP

Ce projet permet de gérer de manière centralisée les règles de pare-feu pour des serveurs Linux et Windows.

## Architecture du MVP
- **Backend** : API Symfony (PHP 8.2+) gérant l'orchestration.
- **Agent** : Binaire Go léger (Onboarding automatisé).
- **Communication** : WebSockets (WSS) + mTLS (Mutual TLS).

## Pré-requis
- Docker & Docker Compose
- PHP 8.3+ & Composer
- Go 1.24+

## Installation Rapide (Développement)

1. **Générer la CA racine** (une seule fois) :
   ```bash
   bash scripts/generate-certs.sh
   ```

2. **Démarrage Serveur** :
   ```bash
   docker-compose up -d
   cd backend
   composer install
   php bin/console doctrine:migrations:migrate
   php bin/websocket-server.php
   ```

3. **Lancement de l'Agent (Onboarding par Token)** :
   *   Générez un token en base de données (table `registration_token`).
   *   Lancez l'agent :
   ```bash
   cd agent
   AGENT_TOKEN=votre-token go run cmd/agent/main.go
   ```
   L'agent va automatiquement générer sa clé privée, son CSR, et obtenir son certificat signé via l'API Bootstrap.

## Sécurité
- L'API Bootstrap transmet le token. En production, assurez-vous que le backend Symfony est exposé via **HTTPS** (Reverse Proxy type Nginx/Traefik).
- Le tunnel WebSocket utilise obligatoirement le **mTLS**.
