# Stack Technique et Stratégie de Sécurité

## 1. Stack Technique Recommandée

| Composant | Technologie | Justification |
| :--- | :--- | :--- |
| **Backend API** | **PHP 8.2+ / Symfony 6/7** | Framework robuste, composant Security puissant, gestion native du mTLS, intégration facile avec PostgreSQL via Doctrine. |
| **Frontend** | **React / Svelte** | Modernité et réactivité pour un dashboard complexe. |
| **Base de données** | **PostgreSQL** | Fiabilité, support JSONB pour l'extensibilité des règles. |
| **Agents** | **Go (Golang)** | Compilation statique sans dépendances (idéal pour Windows/Linux), performance système, bibliothèques réseau matures. |
| **Communication** | **WSS + mTLS** | WebSocket sécurisé pour le temps réel et passage de NAT. Authentification mutuelle par certificats. |

## 2. Modèle de Données Principal

### 2.1 Serveurs (`Server`)
- `uuid`: Identifiant unique.
- `hostname`: Nom de la machine.
- `os_family`: Linux ou Windows.
- `last_heartbeat`: Dernier signe de vie de l'agent.
- `status`: Online, Offline, Pending, Maintenance.

### 2.2 Règles de Pare-feu (`FirewallRule`)
- `name`, `description`.
- `direction`: Inbound / Outbound.
- `action`: Allow / Deny.
- `protocol`: TCP, UDP, ICMP, Any.
- `priority`.
- `status`: Active, Deployment_Pending, Failed.

## 3. Stratégie de Sécurité

### 3.1 Authentification Forte (mTLS)
- Chaque agent possède un certificat signé par une **CA interne** gérée par Symfony.
- Le serveur rejette toute connexion dont le certificat est invalide ou révoqué.

### 3.2 Signature des Commandes
- Chaque message envoyé est **signé** (Ed25519). L'agent vérifie la signature avant exécution.

### 3.3 Mécanisme Anti-Lockout & Rollback Automatique
- **Application Conditionnelle** : L'agent applique la règle temporairement.
- **Vérification** : Si la connexion au central est coupée, l'agent restaure l'ancien ruleset après 30s.

## 4. Gestion des Conflits et Dérive (Drift)

### 4.1 Détection de Dérive Manuelle
- L'agent calcule régulièrement un **checksum (hash)** de la configuration native du pare-feu.
- Ce hash est envoyé au serveur central lors des Heartbeats.
- Si le hash diffère de celui attendu (basé sur la base de données), le serveur marque le serveur comme "Out-of-sync" et alerte l'administrateur.

### 4.2 Résolution des Conflits
- **Priorité à la Plateforme** : Par défaut, la plateforme est la source unique de vérité. Une synchronisation forcée écrase les règles locales non répertoriées.
- **Règles Protégées** : Possibilité de définir des tags pour ignorer certaines règles locales pré-existantes (ex: règles Docker, Cloud provider).

## 5. Journalisation et Audit
- Table d'audit immuable : `Qui`, `Quoi`, `Quand`, `Résultat`.
