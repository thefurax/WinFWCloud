# Spécifications de l'Agent et Cycle de Vie des Règles

## 1. Cycle de Vie d'une Politique (Policy-Based)

Le système ne gère plus les règles à l'unité, mais par **Politique**.

### 1.1 Application d'une Politique
1. **Émission** : Déploiement via `APPLY_POLICY`.
2. **Application Atomique** : L'agent applique le ruleset entier.

## 2. Pseudo-code de l'Agent (Policy-Based)

```go
func handleApplyPolicy() {
    // Logic for atomic application
}
```

### 2.1 Moteur Linux
L'agent Linux utilise **nftables**.

### 2.2 Moteur Windows
L'agent Windows utilise **PowerShell** Defender API.

## 3. Stratégie de Rollback
Rollback automatique après 30s de perte de connectivité.
