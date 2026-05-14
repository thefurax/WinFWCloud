# Spécifications de l'Agent et Cycle de Vie des Règles

## 1. Cycle de Vie d'une Règle

### 1.1 Ajout / Modification d'une Règle
1. **Émission** : L'administrateur valide la règle sur le frontend.
2. **Orchestration** : Le serveur Symfony signe le payload et l'envoie via WebSocket à l'agent cible.
3. **Réception** : L'agent vérifie la signature Ed25519.
4. **Dry-run (Optionnel)** : L'agent simule l'application et renvoie le résultat sans modifier le système.
5. **Sauvegarde de l'état** : L'agent exporte la configuration actuelle (ex: `nft list ruleset > backup.nft`).
6. **Application Temporaire** : L'agent applique la règle.
7. **Test de Connectivité** : L'agent vérifie qu'il peut toujours communiquer avec le serveur Symfony.
8. **Commit / Rollback** :
    - Si succès : La règle est maintenue.
    - Si échec (Perte de lien) : L'agent restaure `backup.nft` après un timeout de 30s.

### 1.2 Suppression d'une Règle
Similaire à l'ajout, mais avec la commande de suppression. Le rollback automatique s'applique également pour éviter de couper l'accès en supprimant une règle d'autorisation critique.

## 2. Pseudo-code de l'Agent (Go - Linux/nftables)

```go
// Structure simplifiée de l'agent Linux
package main

import (
    "crypto/ed25519"
    "encoding/json"
    "os/exec"
)

func handleCommand(message CommandMessage) {
    // 1. Valider la signature
    if !verifySignature(message, serverPublicKey) {
        log.Error("Signature invalide")
        return
    }

    // 2. Backup de l'état actuel
    backupRules()

    // 3. Traduire JSON en commande native nftables
    cmd := translateToNftables(message.Payload.Rules)

    // 4. Appliquer
    err := exec.Command("nft", "-f", cmd).Run()
    if err != nil {
        sendResponse("failed", err.Error())
        return
    }

    // 5. Anti-Lockout : Vérifier le lien avec le central
    if !checkCentralConnectivity() {
        log.Warn("Perte de connectivité ! Rollback en cours...")
        rollbackRules()
        sendResponse("rollback", "Connectivity lost")
    } else {
        sendResponse("success", "Rules applied")
    }
}
```

## 3. Pseudo-code de l'Agent (PowerShell - Windows)

```powershell
# Logique de gestion du pare-feu Windows
function Apply-FirewallRule($RuleJson) {
    $rule = $RuleJson | ConvertFrom-Json

    try {
        # 1. Création de la règle Windows Defender Firewall
        $params = @{
            DisplayName = $rule.name
            Direction = $rule.direction
            Action = $rule.action
            Protocol = $rule.protocol
            LocalPort = $rule.dst_port
            RemoteAddress = $rule.src_ip
            Enabled = 'True'
        }

        New-NetFirewallRule @params

        # 2. Test de connectivité (Anti-Lockout)
        Start-Sleep -Seconds 5
        if (-not (Test-ConnectionToCentral)) {
            Write-Warning "Rollback de la règle..."
            Remove-NetFirewallRule -DisplayName $rule.name
        }
    } catch {
        Write-Error "Erreur lors de l'application : $($_.Exception.Message)"
    }
}
```

## 4. Stratégie de Rollback Détaillée

| Scénario | Action de l'Agent |
| :--- | :--- |
| Erreur de syntaxe JSON | Rejet immédiat, pas de modification système. |
| Erreur d'exécution native | Restauration immédiate du backup si une modification partielle a eu lieu. |
| Perte de connectivité réseau | Rollback automatique après un délai de grâce (ex: 30-60s) pour permettre la reconnexion. |
| Crash de l'agent pendant l'application | Au redémarrage, l'agent vérifie la présence d'un fichier "transaction en cours" et restaure le dernier backup connu si nécessaire. |
