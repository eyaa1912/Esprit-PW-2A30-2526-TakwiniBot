import os
import glob

workspace = r'c:\Users\21695\Downloads\Hilux-1.0.0\Hilux-1.0.0'
html_files = glob.glob(os.path.join(workspace, '*.html'))

modal_html = """
	<!-- Modal Inscription -->
	<div class="modal fade" id="inscriptionModal" tabindex="-1" role="dialog" aria-labelledby="inscriptionModalLabel" aria-hidden="true" style="z-index: 99999;">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="inscriptionModalLabel">Formulaire d'inscription</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <form>
	          <div class="form-group" style="text-align: left;">
	            <label for="cin">CIN</label>
	            <input type="text" class="form-control" id="cin" placeholder="Votre CIN" required>
	          </div>
	          <div class="form-group" style="text-align: left;">
	            <label for="nom">Nom</label>
	            <input type="text" class="form-control" id="nom" placeholder="Votre Nom" required>
	          </div>
	          <div class="form-group" style="text-align: left;">
	            <label for="prenom">Prénom</label>
	            <input type="text" class="form-control" id="prenom" placeholder="Votre Prénom" required>
	          </div>
	          <div class="form-group" style="text-align: left;">
	            <label for="email">Gmail</label>
	            <input type="email" class="form-control" id="email" placeholder="Votre adresse Gmail" required>
	          </div>
	        </form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
	        <button type="button" class="btn btn-serach-bg" style="background-color: #3bafda; color: #fff;">S'inscrire</button>
	      </div>
	    </div>
	  </div>
	</div>
"""

for filepath in html_files:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        changed = False

        # Add the button dynamically next to the price if it exists
        if 'Certifiant <span>300 DT</span>' in content and 'data-target="#inscriptionModal"' not in content:
            new_price_block = 'Certifiant <span>300 DT</span><br><a href="#" data-toggle="modal" data-target="#inscriptionModal" class="btn btn-serach-bg" style="display:inline-block; margin-top:15px; padding: 5px 20px; font-size: 14px; background-color: #3bafda; color: #fff; border-radius: 4px;">Inscription</a>'
            content = content.replace('Certifiant <span>300 DT</span>', new_price_block)
            changed = True
        
        # Add the modal code just before </body>
        if changed and 'id="inscriptionModal"' not in content:
            content = content.replace('</body>', f'{modal_html}\n</body>')

        if changed:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {filepath}")
            
    except Exception as e:
        print(f"Error processing {filepath}: {e}")

print("Inscription buttons and modals added.")
