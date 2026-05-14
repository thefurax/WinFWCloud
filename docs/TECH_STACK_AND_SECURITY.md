# Stack Technique et Stratégie de Sécurité

## 1. Stack Technique Recommandée

| Composant | Technologie | Justification |
| :--- | :--- | :--- |
| **Backend API** | **PHP 8.2+ / Symfony 6/7** | Framework robuste, composant Security puissant, gestion native du mTLS, intégration facile avec PostgreSQL via Doctrine. |
| **Frontend** | **React / Svelte** | Modernité et réactivité pour un dashboard complexe. |
| **Base de données** | **PostgreSQL** | Fiabilité, support JSONB pour l'extensibilité des règles. |
| **Agents** | **Go (Golang)** | Compilation statique sans dépendances (idéal pour Windows/Linux), performance système, bibliothèques réseau matures. |
| **Communication** | **WSS + mTLS** | WebSocket sécurisé pour le temps réel et passage de NAT. Authentification mutuelle par certificats. |

## 2. Modèle de Données Étendu

### 2.1 Serveurs & Groupes
- **Server** : `uuid`, `hostname`, `os_family`, `agent_version`, `status`, `last_seen`.
- **FirewallGroup** : `id`, `name`, `description`. (Relation Many-to-Many avec Server).
- **Policy** : `id`, `name`, `rules` (JSONB ou relation). Une Policy peut être liée à un FirewallGroup ou un Server unique.

### 2.2 Objets Réseau (Bibliothèque)
- **NetworkObject** :
    - `id`, `name`, `type` (IP, Range, CIDR, DNS).
    - `value` (ex: "192.168.1.1").
    - `version` (incrémenté à chaque modif).
- **NetworkObjectGroup** : `id`, `name`. (Contient des NetworkObjects ou d'autres groupes).

### 2.3 Services (Bibliothèque)
- **Service** :
    - `id`, `name`, `protocol` (TCP, UDP, ICMP, Any).
    - `port_start`, `port_end`.
    - `icmp_type`, `icmp_code`.
- **ServiceGroup** : `id`, `name`. (Contient des Services).

### 2.4 Règles de Pare-feu (FirewallRule)
- `id`, `name`, `priority`, `action` (Allow/Deny), `direction` (In/Out).
- **Source** : Relation vers `NetworkObject` ou `NetworkObjectGroup`.
- **Destination** : Relation vers `NetworkObject` ou `NetworkObjectGroup`.
- **Service** : Relation vers `Service` ou `ServiceGroup`.
- `status` (Synced, Pending, Drift, Error).

## 3. Stratégie de Sécurité

### 3.1 Authentification Forte (mTLS)
- Chaque agent possède un certificat signé par une **CA interne** gérée par Symfony.

### 3.2 Signature des Commandes
- Chaque message envoyé est **signé** (Ed25519).

### 3.3 Mécanisme Anti-Lockout & Rollback Automatique
- **Application Conditionnelle** : L'agent applique la règle temporairement.
- **Vérification** : Si la connexion au central est coupée, l'agent restaure l'ancien ruleset après 30s.

## 4. Gestion des Conflits et Dérive (Drift)

### 4.1 Détection de Dérive
- L'agent envoie un hash du ruleset réel. S'il diffère de la DB, état = `Drift`.

### 4.2 Dépendances (Graph de relations)
- Une table de jointure ou un index permet de trouver instantanément toutes les `FirewallRule` utilisant un `NetworkObject` spécifique pour propager les mises à jour.
