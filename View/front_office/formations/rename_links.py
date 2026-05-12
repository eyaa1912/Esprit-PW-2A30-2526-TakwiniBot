import os
import glob

workspace = r'c:\Users\21695\Downloads\Hilux-1.0.0\formations'
html_files = glob.glob(os.path.join(workspace, '*.html'))

# Rename files if they exist
old_prop = os.path.join(workspace, 'property.html')
new_form = os.path.join(workspace, 'formation.html')

old_prop_det = os.path.join(workspace, 'property-details.html')
new_form_det = os.path.join(workspace, 'formation-details.html')

if os.path.exists(old_prop):
    os.rename(old_prop, new_form)

if os.path.exists(old_prop_det):
    os.rename(old_prop_det, new_form_det)

# Update links in all html files in the 'formations' folder
html_files = glob.glob(os.path.join(workspace, '*.html'))

for filepath in html_files:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        changed = False

        if 'href="property.html"' in content:
            content = content.replace('href="property.html"', 'href="formation.html"')
            changed = True
            
        if 'href="property-details.html"' in content:
            content = content.replace('href="property-details.html"', 'href="formation-details.html"')
            # Also change menu text if needed
            content = content.replace('Property Details</a>', 'Détails de la Formation</a>')
            changed = True

        if changed:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated links in {filepath}")
            
    except Exception as e:
        print(f"Error processing {filepath}: {e}")

print("File renamed and links updated in the formations folder.")
