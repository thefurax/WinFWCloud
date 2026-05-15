# Spécifications API et Protocoles de Communication

## 1. Étude Comparative des Options de Communication

| Option | Recommendation |
| :--- | :--- |
| **WebSockets (WSS)** | **Recommandé** (mTLS natif). |

## 2. Protocole de Communication (WebSocket + mTLS)
- **Authentification** : Mutuelle (mTLS).
- **Canal** : WSS (TLS 1.3).

## 3. Protocole de Politique (APPLY_POLICY)

### 3.1 Formats JSON des Messages (Commande)
```json
{
  "agent_id": "hostname-01",
  "command": "APPLY_POLICY",
  "payload": {
    "policy_name": "Web-Servers-Policy",
    "rules": [
      { "name": "HTTP", "action": "allow", "port": 80 },
      { "name": "HTTPS", "action": "allow", "port": 443 }
    ]
  }
}
```

## 4. Endpoints API REST (Symfony)

### 4.1 Gestion des Politiques (`/api/v1/policies`)
- `GET /` : Liste les politiques.

### 4.2 Bibliothèques
- **Network-objects** : Gestion des IPs.
- **Firewall-groups** : Groupement de serveurs.
