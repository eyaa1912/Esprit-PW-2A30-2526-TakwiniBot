import os
import glob
import re

html_files = glob.glob(r'c:\Users\21695\Downloads\Hilux-1.0.0\Hilux-1.0.0\*.html')

for filepath in html_files:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # Update headers
        content = content.replace('<h1>Property</h1>', '<h1>Formations</h1>')
        content = content.replace('<h2>Latest listing</h2>', '<h2>Dernières formations</h2>')
        content = content.replace('<h2>Latest for Rent</h2>', '<h2>Formations suggérées</h2>')
        
        # Replace banner (description text center)
        desc_pattern = re.compile(r'<div class="single_property_description text-center">\s*<span>.*?</span>\s*<span>.*?</span>\s*<span>.*?</span>\s*</div>', re.DOTALL)
        new_desc = '''<div class="single_property_description text-center">\n\t\t\t\t\t\t\t<span><i class="fa fa-users"></i> Nombre de places : 20</span>\n\t\t\t\t\t\t</div>'''
        content = desc_pattern.sub(new_desc, content)
        
        # Replace card title and description
        content_pattern = re.compile(r'<div class="single_property_content">\s*<h4><a href="#">.*?</a></h4>\s*<p>.*?</p>\s*</div>', re.DOTALL)
        new_content = '''<div class="single_property_content">\n\t\t\t\t\t\t\t<h4><a href="#">Développement Web Full Stack</a></h4>\n\t\t\t\t\t\t\t<p>Cette formation couvre HTML, CSS, JavaScript, Bootstrap et introduction au backend (Node.js / PHP). Vous réaliserez des projets pratiques comme un site e-commerce et un tableau de bord admin.</p>\n\t\t\t\t\t\t</div>'''
        content = content_pattern.sub(new_content, content)
        
        # Replace price & text
        price_pattern = re.compile(r'High Meadow Lane Mount Pleasant <span>\$ 170,000</span>')
        content = price_pattern.sub(r'Certifiant <span>300 DT</span>', content)

        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
    except Exception as e:
        print(f"Error processing {filepath}: {e}")

print("Formations cards replaced successfully in all HTML files.")
