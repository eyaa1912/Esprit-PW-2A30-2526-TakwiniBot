import os
import glob

workspace = r'c:\Users\21695\Downloads\Hilux-1.0.0\Hilux-1.0.0'
html_files = glob.glob(os.path.join(workspace, '*.html'))

print(f"Found {len(html_files)} files.")

for filepath in html_files:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # Title replacement
        content = content.replace('<title>Hilux - Real Estate HTML Template</title>', '<title>Takwinibot - Real Estate HTML Template</title>')
        
        # footer Hilux copyright replacement
        content = content.replace('Hilux &copy;', 'Takwinibot &copy;')

        # Menu texts replacements
        content = content.replace('href="property.html" class="nav-link">Property</a>', 'href="property.html" class="nav-link">Formations</a>')
        
        content = content.replace('href="gallery.html">Gallery</a>', 'href="gallery.html">Produits</a>')
        
        content = content.replace('href="blog.html" class="nav-link">Blog</a>', 'href="blog.html" class="nav-link">Entretien</a>')
        
        # Adding Offres and replacing Contact
        target_contact = '<li><a class="nav-link" href="contact.html">Contact</a></li>'
        replacement_contact = '<li><a class="nav-link" href="offres.html">Offres</a></li>\n\t\t\t\t\t\t\t<li><a class="nav-link" href="contact.html">Réclamations</a></li>'
        content = content.replace(target_contact, replacement_contact)

        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
            
    except Exception as e:
        print(f"Error processing {filepath}: {e}")

print("Replacement complete.")
