import os
import json
import re

def check_file_exists(filepath):
    if os.path.exists(filepath):
        print(f"[OK] Fichier trouvé : {filepath}")
        return True
    else:
        print(f"[ERREUR] Fichier manquant : {filepath}")
        return False

def check_no_placeholders(filepath):
    if not os.path.exists(filepath):
        return False
    with open(filepath, 'r') as f:
        content = f.read()
    if "[..." in content or "voir version précédente" in content.lower():
        print(f"[ERREUR] Placeholder trouvé dans {filepath}")
        return False
    print(f"[OK] Aucun placeholder dans {filepath}")
    return True

def check_content(filepath, patterns):
    if not os.path.exists(filepath):
        return False

    with open(filepath, 'r') as f:
        content = f.read()

    all_ok = True
    for pattern in patterns:
        if re.search(pattern, content, re.IGNORECASE | re.MULTILINE):
            print(f"[OK] Section trouvée dans {filepath} : {pattern}")
        else:
            print(f"[ERREUR] Section manquante dans {filepath} : {pattern}")
            all_ok = False
    return all_ok

def validate_json_blocks(filepath):
    if not os.path.exists(filepath):
        return False

    with open(filepath, 'r') as f:
        content = f.read()

    # Extraire les blocs JSON (```json ... ```)
    json_blocks = re.findall(r'```json\s*(.*?)\s*```', content, re.DOTALL)

    all_ok = True
    for block in json_blocks:
        try:
            json.loads(block)
            print(f"[OK] JSON valide dans {filepath}")
        except json.JSONDecodeError as e:
            print(f"[ERREUR] JSON invalide dans {filepath} : {e}")
            all_ok = False
    return all_ok

def main():
    deliverables = [
        "docs/ARCHITECTURE.md",
        "docs/TECH_STACK_AND_SECURITY.md",
        "docs/API_SPEC.md",
        "docs/AGENT_SPEC.md",
        "docs/TEST_CHECKLIST.md",
        "docs/FRONTEND_AND_UX.md"
    ]

    sections = {
        "docs/ARCHITECTURE.md": [
            "Architecture Globale",
            "Schéma Logique",
            "Arborescence du Projet",
            "Roadmap",
            "Symfony"
        ],
        "docs/TECH_STACK_AND_SECURITY.md": [
            "Stack Technique",
            "Modèle de Données",
            "Stratégie de Sécurité",
            "Anti-Lockout",
            "Rollback Automatique",
            "NetworkObject",
            "FirewallGroup"
        ],
        "docs/API_SPEC.md": [
            "Étude Comparative",
            "Protocole de Communication",
            "Formats JSON",
            "Endpoints API REST",
            "Network-objects",
            "Firewall-groups"
        ],
        "docs/AGENT_SPEC.md": [
            "Cycle de Vie",
            "Pseudo-code",
            "Linux",
            "Windows",
            "Rollback"
        ],
        "docs/TEST_CHECKLIST.md": [
            "Tests Fonctionnels",
            "Tests de Sécurité",
            "Anti-Lockout",
            "Intégrité"
        ],
        "docs/FRONTEND_AND_UX.md": [
            "Maquette Logique",
            "Éditeur de Règles",
            "Flux Utilisateur",
            "Gestion des Dépendances",
            "Validation UX"
        ]
    }

    print("--- DÉBUT DE LA VALIDATION DU LIVRABLE ---")

    success = True
    for d in deliverables:
        if not check_file_exists(d):
            success = False
            continue

        if not check_no_placeholders(d):
            success = False

        if not check_content(d, sections[d]):
            success = False

        if not validate_json_blocks(d):
            success = False

    if success:
        print("\n--- TOUS LES LIVRABLES SONT VALIDES ---")
        exit(0)
    else:
        print("\n--- DES ERREURS ONT ÉTÉ DÉTECTÉES ---")
        exit(1)

if __name__ == "__main__":
    main()
