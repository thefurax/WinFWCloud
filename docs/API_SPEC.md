# Spécifications API et Protocoles de Communication

## 1. Étude Comparative des Options de Communication
- **WebSockets (WSS)** : Recommandé.

## 2. Protocole de Communication (WebSocket + mTLS)
- Authentification : mTLS.

## 3. Formats JSON des Messages

### 3.1 Commande APPLY_POLICY
```json
{
  "agent_id": "hostname-01",
  "command": "APPLY_POLICY",
  "payload": {
    "policy_name": "Standard-Policy",
    "rules": [
      { "name": "HTTP", "ip_version": "ipv4", "direction": "inbound", "action": "allow", "port": 80 }
    ]
  }
}
```

## 4. Endpoints API REST (Symfony)

### 4.1 Bibliothèques
- **Network-objects** : IPs/CIDRs.
- **Firewall-groups** : Groupes de serveurs.
