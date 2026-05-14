# Plan de Développement - Firewall Central

## 1. Synthèse de l'Architecture Existante
- **Modèle** : Agent-Serveur (Control Plane / Data Plane).
- **Backend** : Symfony (PHP 8.2+) + PostgreSQL.
- **Agent** : Go (Compilation statique Windows/Linux).
- **Communication** : WebSockets sécurisés (WSS) + mTLS + Signature Ed25519.
- **Sécurité** : Anti-Lockout (Rollback automatique si perte de lien).
- **Source de Vérité** : Plateforme centrale avec détection de dérive (Drift).

## 2. Zones Floues / À Préciser
- **Gestion des Certificats** : Préciser le moteur de PKI (ex: OpenSSL natif via PHP ou outil externe).
- **Format du Ruleset natif** : Comment l'agent stocke le backup (fichier temporaire vs mémoire).
- **Interface Web** : Préciser les composants UI (Shadcn/UI recommandé).

## 3. Découpage en Lots de Travail (Work Packages)

### Lot 1 : Socle Infrastructure & Sécurité (Semaines 1-2)
- Configuration Docker du projet.
- Initialisation du Backend Symfony (Entités Server, Rule).
- Mise en place de la PKI interne (Génération de certificats agents).
- Serveur WebSocket avec mTLS.

### Lot 2 : Agent Minimal (Semaines 2-3)
- Squelette d'agent en Go.
- Gestion de la connexion WSS + mTLS.
- Réception et validation de signature Ed25519.
- Système de Heartbeat.

### Lot 3 : Moteurs de Pare-feu (Semaines 3-4)
- Abstraction Linux (nftables).
- Abstraction Windows (PowerShell).
- Implémentation du Rollback / Anti-Lockout.

### Lot 4 : Interface & Dashboard (Semaines 4-5)
- Dashboard React.
- Création/Édition de règles.
- Visualisation des serveurs et de leur état de dérive.

## 4. Contenu du MVP (Minimum Viable Product)
- Backend Symfony avec API REST basique.
- Agent Go capable de :
  - Se connecter en mTLS.
  - Appliquer une règle "Allow Port" sur Linux (nftables) et Windows.
  - Faire un rollback si la connexion est perdue.
- Interface web listant les serveurs et permettant d'ajouter une règle simple.

## 5. Arborescence Finale du Projet
```text
firewall-central/
├── agent/                  # Go Source
│   ├── cmd/agent/          # Entry point
│   ├── internal/
│   │   ├── firewall/       # OS abstractions (linux, windows)
│   │   ├── transport/      # WSS, mTLS
│   │   └── security/       # Ed25519 validation
│   └── go.mod
├── backend/                # Symfony Source
│   ├── bin/console
│   ├── config/
│   ├── src/
│   │   ├── Controller/     # API Endpoints
│   │   ├── Entity/         # Server, Rule, Audit
│   │   ├── Security/       # mTLS Authenticator
│   │   └── Service/        # PKI, Messenger handlers
│   └── composer.json
├── frontend/               # React Source
├── scripts/                # Setup & Dev tools
└── docker-compose.yml
```

## 6. Dépendances & Commandes d'Installation

### Backend (Symfony)
- **Dépendances** : `php-amqp`, `openssl`, `doctrine`, `lexik/jwt-authentication-bundle`.
- **Installation** :
  ```bash
  cd backend
  composer install
  php bin/console doctrine:database:create
  php bin/console doctrine:migrations:migrate
  ```

### Agent (Go)
- **Dépendances** : `gorilla/websocket`, `google.golang.org/crypto`.
- **Compilation** :
  ```bash
  cd agent
  go mod tidy
  GOOS=linux GOARCH=amd64 go build -o agent-linux
  GOOS=windows GOARCH=amd64 go build -o agent-windows.exe
  ```

## 7. Premières Tâches à Coder
1. **Initialisation Backend** : Structure Symfony + Entité `Server`.
2. **PKI Service** : Service de génération de certificats client.
3. **Agent Hello World** : Connexion WSS simple (sans mTLS au début) pour valider le canal.
4. **Modèle de données** : Table `firewall_rules` et lien avec `Server`.
