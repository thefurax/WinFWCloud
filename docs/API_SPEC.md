# Spécifications API et Protocoles de Communication

## 1. Étude Comparative des Options de Communication

| Option | Avantages | Inconvénients | Recommandation |
| :--- | :--- | :--- | :--- |
| **HTTP Polling** (L'agent interroge l'API) | Simple à implémenter, traverse facilement les pare-feu. | Latence élevée, charge inutile sur le serveur, pas de temps réel. | **Non retenu** pour l'application des règles critiques. |
| **Server Push** (SSE) | Unidirectionnel efficace, standard HTTP. | Difficile pour les agents derrière un NAT agressif, pas de retour d'état natif. | **Non retenu**. |
| **Message Broker** (MQTT/RabbitMQ) | Scalabilité massive, persistance des messages. | Ajoute une dépendance lourde, gestion complexe du mTLS sur le broker. | Option secondaire pour très gros parcs. |
| **WebSockets (WSS)** | Bi-directionnel, temps réel, passage de NAT (agent vers serveur). | Nécessite une gestion des reconnexions et du stateful côté serveur. | **Recommandé** pour sa réactivité et sa simplicité d'intégration mTLS. |

## 2. Protocole de Communication (WebSocket + mTLS)

La communication entre le serveur central (Symfony) et les agents (Go) s'effectue via un tunnel **WebSocket sécurisé**.
- **Canal Sortant** : L'agent initie la connexion (évite d'ouvrir des ports entrants sur les serveurs cibles).
- **Authentification** : Mutuelle via certificats X.509 (mTLS).

## 3. Formats JSON des Messages

### 3.1 Commande envoyée par le serveur (`CommandMessage`)
```json
{
  "header": {
    "msg_id": "uuid-v4",
    "timestamp": "2023-10-27T14:30:00Z",
    "command": "APPLY_RULES",
    "signature": "ed25519-signature-string"
  },
  "payload": {
    "dry_run": false,
    "transaction_id": "tx-9876",
    "rules": [
      {
        "id": "rule-1",
        "action": "allow",
        "direction": "inbound",
        "protocol": "tcp",
        "dst_port": 443,
        "src_ip": "0.0.0.0/0"
      }
    ]
  }
}
```

### 3.2 Réponse renvoyée par l'agent (`ResponseMessage`)
```json
{
  "header": {
    "msg_id": "uuid-v4",
    "correlation_id": "uuid-v4-original",
    "timestamp": "2023-10-27T14:30:05Z"
  },
  "payload": {
    "status": "success",
    "message": "Rules applied successfully",
    "details": {
      "execution_log": ["Rule 1 added to nftables"],
      "os": "linux",
      "firewall_engine": "nftables"
    }
  }
}
```

## 4. Endpoints API REST (Symfony)

### 4.1 Gestion des Serveurs
- `GET /api/v1/servers` : Liste des serveurs et état.
- `POST /api/v1/servers/{uuid}/sync` : Force une synchronisation.

### 4.2 Gestion des Règles
- `POST /api/v1/firewall-rules` : Créer une règle.
- `POST /api/v1/firewall-rules/{id}/deploy` : Déployer.

## 5. Sécurité
- **Authentification** : JWT pour les admins, mTLS pour les agents.
- **Autorisation** : RBAC (Admin, Operateur, Lecture seule).
