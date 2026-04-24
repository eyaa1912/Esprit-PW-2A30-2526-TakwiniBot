#!/usr/bin/env python3
"""
Script pour appliquer les corrections finales:
1. Ajouter logout-green.css
2. Changer les liens vers mon-profil.php
3. Ajouter la classe logout-btn au bouton déconnexion
"""

import os
import re
from pathlib import Path

def process_file(filepath):
    """Traite un fichier HTML/PHP"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        modified = False
        
        # 1. Ajouter logout-green.css si pas déjà présent
        if 'logout-green.css' not in content and 'custom-dropdown.css' in content:
            content = content.replace(
                '<link rel="stylesheet" href="../assets/css/ripple-effect.css" />',
                '<link rel="stylesheet" href="../assets/css/ripple-effect.css" />\n    <link rel="stylesheet" href="../assets/css/logout-green.css" />'
            )
            modified = True
        
        # 2. Changer pages-account-settings-account.html vers mon-profil.php
        if 'pages-account-settings-account.html' in content:
            content = content.replace(
                'href="pages-account-settings-account.html"',
                'href="mon-profil.php"'
            )
            modified = True
        
        # 3. Ajouter classe logout-btn au bouton déconnexion
        # Pattern: <a class="dropdown-item" href="...">...<i class="...bx-power-off...
        pattern = r'(<a class="dropdown-item"[^>]*>[\s\S]*?<i class="[^"]*bx-power-off[^"]*")'
        if re.search(pattern, content):
            content = re.sub(
                r'<a class="dropdown-item"([^>]*>[\s\S]*?bx-power-off)',
                r'<a class="dropdown-item logout-btn"\1',
                content
            )
            modified = True
        
        # Sauvegarder si modifié
        if modified and content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"  ✅ Modifié: {filepath.name}")
            return True
        else:
            if 'logout-green.css' in content:
                print(f"  ⏭️  Déjà à jour: {filepath.name}")
            else:
                print(f"  ⚠️  Pas de modification: {filepath.name}")
            return False
            
    except Exception as e:
        print(f"  ❌ Erreur avec {filepath.name}: {str(e)}")
        return False

def main():
    """Fonction principale"""
    print("🚀 Application des corrections finales\n")
    
    base_dir = Path(__file__).parent.parent
    html_dir = base_dir / 'html'
    
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
    
    # Résumé
    print(f"\n{'='*60}")
    print(f"✨ Résumé:")
    print(f"   Total de fichiers traités: {total}")
    print(f"   Fichiers modifiés: {modified}")
    print(f"   Fichiers déjà à jour: {total - modified}")
    print(f"{'='*60}\n")
    
    if modified > 0:
        print("✅ Les corrections ont été appliquées avec succès!")
        print("\n📝 Changements appliqués:")
        print("   1. ✅ CSS logout-green.css ajouté")
        print("   2. ✅ Liens vers mon-profil.php mis à jour")
        print("   3. ✅ Classe logout-btn ajoutée au bouton déconnexion")
        print("\n🧪 Testez maintenant:")
        print("   - Le menu reste ouvert 2 secondes")
        print("   - Le bouton Déconnexion est en vert")
        print("   - Le lien Profil ouvre mon-profil.php")
    else:
        print("ℹ️  Aucune modification nécessaire - tous les fichiers sont déjà à jour")

if __name__ == '__main__':
    main()
