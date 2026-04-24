#!/usr/bin/env python3
"""
Script pour appliquer les améliorations du dropdown à toutes les pages HTML
"""

import os
import re
from pathlib import Path

# CSS à ajouter dans le head
CSS_LINKS = '''
    <!-- Custom Dropdown Styles -->
    <link rel="stylesheet" href="../assets/css/custom-dropdown.css" />
    <link rel="stylesheet" href="../assets/css/ripple-effect.css" />
'''

# JavaScript à ajouter avant </body>
JS_SCRIPT = '''
    <!-- Custom Dropdown Behavior -->
    <script src="../assets/js/custom-dropdown.js"></script>
'''

def process_file(filepath):
    """Traite un fichier HTML pour ajouter les améliorations"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        modified = False
        
        # Vérifier si les modifications sont déjà présentes
        if 'custom-dropdown.css' in content:
            print(f"  ⏭️  Déjà modifié: {filepath.name}")
            return False
        
        # Ajouter les CSS avant </head>
        if '</head>' in content:
            # Trouver la position de </head>
            head_pos = content.find('</head>')
            if head_pos != -1:
                # Insérer les CSS juste avant </head>
                content = content[:head_pos] + CSS_LINKS + '\n  ' + content[head_pos:]
                modified = True
        
        # Ajouter le JavaScript avant </body>
        if '</body>' in content:
            # Trouver la position de </body>
            body_pos = content.find('</body>')
            if body_pos != -1:
                # Insérer le JS juste avant </body>
                content = content[:body_pos] + JS_SCRIPT + '\n\n  ' + content[body_pos:]
                modified = True
        
        # Sauvegarder si modifié
        if modified and content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"  ✅ Modifié: {filepath.name}")
            return True
        else:
            print(f"  ⚠️  Pas de modification: {filepath.name}")
            return False
            
    except Exception as e:
        print(f"  ❌ Erreur avec {filepath.name}: {str(e)}")
        return False

def main():
    """Fonction principale"""
    print("🚀 Application des améliorations du dropdown utilisateur\n")
    
    # Répertoire de base
    base_dir = Path(__file__).parent.parent
    html_dir = base_dir / 'html'
    
    # Compteurs
    total = 0
    modified = 0
    
    # Traiter tous les fichiers HTML et PHP
    patterns = ['*.html', '*.php']
    
    for pattern in patterns:
        files = list(html_dir.glob(pattern))
        
        if files:
            print(f"\n📁 Traitement des fichiers {pattern}:")
            for filepath in sorted(files):
                total += 1
                if process_file(filepath):
                    modified += 1
    
    # Traiter aussi les sous-dossiers
    subdirs = ['../formations', '../entretiens', '../offres', '../produits', 
               '../reclamations', '../utilisateurs', '../formations-module']
    
    for subdir in subdirs:
        subdir_path = base_dir / subdir
        if subdir_path.exists():
            print(f"\n📁 Traitement du dossier {subdir}:")
            for pattern in patterns:
                files = list(subdir_path.glob(pattern))
                for filepath in sorted(files):
                    total += 1
                    if process_file(filepath):
                        modified += 1
    
    # Résumé
    print(f"\n{'='*60}")
    print(f"✨ Résumé:")
    print(f"   Total de fichiers traités: {total}")
    print(f"   Fichiers modifiés: {modified}")
    print(f"   Fichiers déjà à jour: {total - modified}")
    print(f"{'='*60}\n")
    
    if modified > 0:
        print("✅ Les améliorations ont été appliquées avec succès!")
        print("\n📝 Prochaines étapes:")
        print("   1. Testez les pages dans votre navigateur")
        print("   2. Vérifiez que le dropdown reste ouvert plus longtemps")
        print("   3. Testez les animations et effets visuels")
    else:
        print("ℹ️  Aucune modification nécessaire - tous les fichiers sont déjà à jour")

if __name__ == '__main__':
    main()
