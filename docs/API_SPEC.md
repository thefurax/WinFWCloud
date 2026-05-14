# Spécifications API et Protocoles de Communication

## 1. Étude Comparative des Options de Communication

| Option | Avantages | Inconvénients | Recommandation |
| :--- | :--- | :--- | :--- |
| **HTTP Polling** | Simple, traverse les pare-feu. | Latence élevée, charge serveur. | Non retenu. |
| **Server Push (SSE)** | Unidirectionnel efficace. | Difficile derrière NAT. | Non retenu. |
| **Message Broker** | Scalabilité. | Dépendance lourde. | Option secondaire. |
| **WebSockets (WSS)** | Bi-directionnel, temps réel, traverse les NAT (sortant). | Stateful côté serveur. | **Recommandé** (mTLS natif). |

## 2. Protocole de Communication (WebSocket + mTLS)

- **Authentification** : Mutuelle via certificats X.509 signés par la CA interne du serveur Symfony.
- **Canal** : WSS (WebSocket over TLS 1.3). L'agent initie la connexion.
- **Payload** : JSON structuré et signé avec Ed25519 côté serveur.

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
        "src_ip": "10.0.0.0/8"
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

## 4. Endpoints API REST (Bibliothèques et Gestion)

### 4.1 Objets Réseau (`/api/v1/network-objects`)
- `GET /` : Liste les objets avec filtres.
- `POST /` : Créer un objet (IP, CIDR, Range, DNS).
- `GET /{id}/usages` : Liste les règles/politiques impactées.
- `DELETE /{id}` : Supprime (si non utilisé).

### 4.2 Services (`/api/v1/services`)
- `GET /` : Liste les services.
- `POST /` : Créer un service (Protocol, Port/Range).

### 4.3 Groupes et Politiques
- `GET /api/v1/firewall-groups` : Liste les groupes de serveurs.
- `PATCH /api/v1/firewall-groups/{id}/policy` : Assigner une politique.

## 5. Sécurité
- **RBAC** : `ROLE_ADMIN`, `ROLE_OPERATOR`, `ROLE_VIEWER`.
- **Validation** : Validation stricte via Symfony Validator.
