# Projet Firewall Central - MVP

Ce projet permet de gérer de manière centralisée les règles de pare-feu pour des serveurs Linux et Windows.

## Architecture du MVP
- **Backend** : API Symfony (PHP 8.2+) gérant l'orchestration.
- **Agent** : Binaire Go léger installé sur les serveurs cibles.
- **Communication** : WebSockets (WSS) + mTLS (simulé dans ce squelette).

## Pré-requis
- Docker & Docker Compose
- PHP 8.2+ & Composer
- Go 1.24+

## Installation Rapide

1. **Lancer l'infrastructure (Base de données)** :
   ```bash
   docker-compose up -d
   ```

2. **Configurer le Backend** :
   ```bash
   cd backend
   composer install
   php bin/console doctrine:migrations:migrate
   ```

3. **Lancer l'Agent** :
   ```bash
   cd agent
   go run cmd/agent/main.go
   ```

## API Endpoints (Exemples)

### Enregistrement d'un agent
`POST /api/v1/agent/register`
```json
{
  "hostname": "srv-web-01",
  "os": "linux"
}
```

### Heartbeat
`POST /api/v1/agent/heartbeat`

## Structure du Projet
- `agent/` : Code source de l'agent Go.
- `backend/` : Code source de l'API Symfony.
- `scripts/` : Utilitaires de développement et validation.
- `docs/` : Documentation technique et exemples de messages.
