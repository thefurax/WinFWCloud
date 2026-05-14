# Checklist de Tests Fonctionnels et de Sécurité

## 1. Tests Fonctionnels

### 1.1 Gestion des Agents
- [ ] L'agent se connecte avec succès au serveur central (mTLS).
- [ ] Le serveur détecte la déconnexion de l'agent après un timeout.
- [ ] L'agent remonte correctement ses métadonnées (OS, Version, Hostname).

### 1.2 Gestion des Règles
- [ ] Ajout d'une règle (Allow/Deny) sur un agent Linux (nftables).
- [ ] Ajout d'une règle (Allow/Deny) sur un agent Windows (Defender Firewall).
- [ ] Suppression d'une règle existante.
- [ ] Modification d'une règle (changement de port ou d'IP).
- [ ] Déploiement d'une règle sur un groupe de serveurs.

### 1.3 Anti-Lockout et Rollback
- [ ] L'agent effectue un rollback automatique si une règle coupe le port SSH/RDP.
- [ ] L'agent effectue un rollback automatique si le serveur central devient injoignable.
- [ ] Le mode "Dry-run" simule l'application sans modifier réellement le système.

## 2. Tests de Sécurité

### 2.1 Authentification et Chiffrement
- [ ] Tentative de connexion avec un certificat expiré (Rejet attendu).
- [ ] Tentative de connexion avec un certificat non signé par la CA interne (Rejet attendu).
- [ ] Vérification que tout le trafic WebSocket est chiffré en TLS 1.3.

### 2.2 Intégrité des Commandes
- [ ] Envoi d'une commande sans signature (Rejet attendu par l'agent).
- [ ] Envoi d'une commande avec une signature corrompue (Rejet attendu par l'agent).
- [ ] Tentative d'injection de commande shell dans les champs JSON (Validation stricte).

### 2.3 Résilience et Audit
- [ ] Vérification que chaque action administrative est présente dans la table `audit_logs`.
- [ ] Vérification de l'étanchéité des rôles (ex: un OPERATEUR ne peut pas créer d'utilisateurs).
- [ ] Test de montée en charge (scalabilité) avec 100 agents connectés simultanément.
