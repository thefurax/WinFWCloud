# Spécifications Frontend et Expérience Utilisateur (UX)

## 1. Maquette Logique de l'Interface

Structure en trois colonnes : Navigation latérale (Arbres), Zone de travail centrale, Panneau d'informations/audit à droite (optionnel).

```text
+---------------------------------------------------------------------------------------+
|  TOP BAR: Recherche Globale | Statut mTLS | Utilisateur | Notifications               |
+---------------------------------------------------------------------------------------+
| ARBORESCENCE (GAUCHE)    | ZONE DE TRAVAIL (DROITE)                                   |
|                          |                                                            |
| [1] Pare-feu / Groupes   | +-------------------------------------------------------+  |
|     - Prod (Gp)          | | Titre : Politique Groupe Production                   |  |
|     - Srv-Web-01         | +-------------------------------------------------------+  |
| [2] Objets Réseau        | | Barre d'actions: [Ajouter] [Dry-Run] [Appliquer]      |  |
|     - Net_Internal       | +-------------------------------------------------------+  |
| [3] Services             | | Tableau des Règles (Priorité, Src, Dst, Svc, Action)  |  |
|     - HTTP_Secure        | | 10 | Net_Int | Web_Srv | HTTPS | Allow  | [Edit]     |  |
+--------------------------+ +-------------------------------------------------------+  |
```

## 2. Éditeur de Règles Visuel

Chaque règle est composée de :
- **Source/Destination** : Champ auto-complété acceptant du texte libre (IP/CIDR) ou des références à la bibliothèque d'objets (Drag-and-drop supporté).
- **Service** : Sélection d'un service prédéfini ou définition manuelle (Protocole + Port).
- **Action** : Toggle "Allow" (Vert) / "Deny" (Rouge).
- **Direction** : "Inbound" / "Outbound".

## 3. Gestion des Dépendances

- **Impact Analysis** : Lors de la modification d'un objet réseau, une fenêtre modale liste tous les serveurs et règles impactés.
- **Référencement Inverse** : Sur la fiche d'un service (ex: SSH), un onglet "Utilisations" liste toutes les politiques où il est présent.
- **Verrouillage** : Le bouton "Supprimer" est désactivé si l'objet est lié à une règle active.

## 4. Flux Utilisateur : Application d'une politique
1. L'utilisateur modifie une règle dans une politique de groupe.
2. Le système calcule les différences et affiche un "Diff" visuel.
3. L'utilisateur clique sur **Dry-run**.
4. Le système interroge les agents (sans appliquer) et affiche "Simulation OK" ou les erreurs potentielles.
5. L'utilisateur clique sur **Appliquer**.
6. Suivi en temps réel de la progression sur chaque machine du groupe.

## 5. Validation UX
- **Performance** : Utilisation de `react-window` pour l'affichage de listes massives de serveurs.
- **Sécurité** : Demande de confirmation par mot de passe ou MFA pour les changements impactant le groupe "Infrastructure Critique".
- **Clarté** : Utilisation de badges pour distinguer les règles héritées (lecture seule) des règles locales.
