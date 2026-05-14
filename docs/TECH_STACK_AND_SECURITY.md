# Stack Technique et Stratégie de Sécurité

## 1. Stack Technique Recommandée

| Composant | Technologie | Justification |
| :--- | :--- | :--- |
| **Backend API** | **PHP 8.2+ / Symfony 6/7** | Framework robuste, composant Security puissant, gestion native du mTLS, intégration facile avec PostgreSQL via Doctrine. |
| **Frontend** | **React / Svelte** | Modernité et réactivité pour un dashboard complexe. |
| **Base de données** | **PostgreSQL** | Fiabilité, support JSONB pour l'extensibilité des règles. |
| **Agents** | **Go (Golang)** | Compilation statique sans dépendances, performance système. |
| **Communication** | **WSS + mTLS** | WebSocket sécurisé pour le temps réel et passage de NAT. |

## 2. Modèle de Données

### 2.1 Serveurs & Groupes (NetworkObject, FirewallGroup)
- **Server** : `uuid`, `hostname`, `os_family`, `agent_version`, `status`, `last_seen`.
- **FirewallGroup** : `id`, `name`, `description`.
- **RegistrationToken** : `token`, `expires_at`, `used`.

## 3. Stratégie de Sécurité (Transparent mTLS)

Le système utilise une PKI interne pour sécuriser les communications de manière transparente.

### 3.1 Enrôlement via Token (Onboarding)
1. **Génération** : L'administrateur génère un token sur le dashboard.
2. **Bootstrap** : L'agent génère localement sa paire de clés (RSA 2048) et un CSR.
3. **Signature** : L'agent envoie le CSR au serveur via API HTTPS. Le serveur valide le token, signe le certificat et le renvoie.

### 3.2 Anti-Lockout et Rollback Automatique
- **Application Temporaire** : L'agent applique la règle. S'il perd le lien avec le serveur central, il restaure automatiquement la configuration précédente après 30s.

## 4. Gestion des Conflits
- Détection de dérive (Drift) par checksum du ruleset local.
