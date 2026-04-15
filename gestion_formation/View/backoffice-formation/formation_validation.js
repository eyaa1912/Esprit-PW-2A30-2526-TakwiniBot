/**
 * Validation personnalisée pour les formulaires de formation
 * Sans utiliser les attributs HTML5 (required, min, max, etc.)
 */

// Fonction pour afficher les erreurs
function showError(input, message) {
  // Supprimer l'ancienne erreur si elle existe
  const existingError = input.parentElement.querySelector('.error-message');
  if (existingError) {
    existingError.remove();
  }
  
  // Ajouter la classe d'erreur
  input.classList.add('is-invalid');
  
  // Créer et afficher le message d'erreur
  const errorDiv = document.createElement('div');
  errorDiv.className = 'error-message text-danger small mt-1';
  errorDiv.textContent = message;
  input.parentElement.appendChild(errorDiv);
}

// Fonction pour supprimer les erreurs
function clearError(input) {
  input.classList.remove('is-invalid');
  const errorDiv = input.parentElement.querySelector('.error-message');
  if (errorDiv) {
    errorDiv.remove();
  }
}

// Validation du titre : ne doit pas contenir de caractères spéciaux
function validateTitre(titre) {
  if (!titre || titre.trim() === '') {
    return { valid: false, message: 'Le titre est obligatoire.' };
  }
  
  // Vérifier qu'il ne contient que des lettres, chiffres et espaces
  const regex = /^[a-zA-Z0-9\sàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ]+$/;
  if (!regex.test(titre)) {
    return { valid: false, message: 'Le titre ne doit pas contenir de caractères spéciaux.' };
  }
  
  if (titre.length < 3) {
    return { valid: false, message: 'Le titre doit contenir au moins 3 caractères.' };
  }
  
  if (titre.length > 100) {
    return { valid: false, message: 'Le titre ne doit pas dépasser 100 caractères.' };
  }
  
  return { valid: true };
}

// Validation de la durée : ne doit pas dépasser 2 ans
function validateDuree(duree) {
  if (!duree || duree.trim() === '') {
    return { valid: false, message: 'La durée est obligatoire.' };
  }
  
  const dureeStr = duree.toLowerCase();
  
  // Extraire les nombres de la chaîne
  const numbers = dureeStr.match(/\d+/g);
  if (!numbers) {
    return { valid: false, message: 'La durée doit contenir un nombre (ex: 3 mois, 40 heures).' };
  }
  
  const value = parseInt(numbers[0]);
  
  // Vérifier si c'est en années
  if (dureeStr.includes('an') || dureeStr.includes('year')) {
    if (value > 2) {
      return { valid: false, message: 'La durée ne peut pas dépasser 2 ans.' };
    }
  }
  
  // Vérifier si c'est en mois
  if (dureeStr.includes('mois') || dureeStr.includes('month')) {
    if (value > 24) {
      return { valid: false, message: 'La durée ne peut pas dépasser 24 mois (2 ans).' };
    }
  }
  
  // Vérifier si c'est en jours
  if (dureeStr.includes('jour') || dureeStr.includes('day')) {
    if (value > 730) {
      return { valid: false, message: 'La durée ne peut pas dépasser 730 jours (2 ans).' };
    }
  }
  
  return { valid: true };
}

// Validation du prix : ne doit pas dépasser 9000 TND
function validatePrix(prix) {
  if (prix === '' || prix === null || prix === undefined) {
    return { valid: false, message: 'Le prix est obligatoire.' };
  }
  
  const prixNum = parseFloat(prix);
  
  if (isNaN(prixNum)) {
    return { valid: false, message: 'Le prix doit être un nombre valide.' };
  }
  
  if (prixNum < 0) {
    return { valid: false, message: 'Le prix ne peut pas être négatif.' };
  }
  
  if (prixNum > 9000) {
    return { valid: false, message: 'Le prix ne peut pas dépasser 9000 TND.' };
  }
  
  return { valid: true };
}

// Validation du niveau : doit être sélectionné (obligatoire)
function validateNiveau(niveau) {
  if (!niveau || niveau === '') {
    return { valid: false, message: 'Le niveau est obligatoire.' };
  }
  
  const niveauxValides = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert'];
  if (!niveauxValides.includes(niveau)) {
    return { valid: false, message: 'Veuillez sélectionner un niveau valide.' };
  }
  
  return { valid: true };
}

// Validation de la description : seulement texte et chiffres
function validateDescription(description) {
  // La description est optionnelle
  if (!description || description.trim() === '') {
    return { valid: true };
  }
  
  // Vérifier qu'elle ne contient que des lettres, chiffres, espaces et ponctuation de base
  const regex = /^[a-zA-Z0-9\s.,;:!?()àâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\-'"]+$/;
  if (!regex.test(description)) {
    return { valid: false, message: 'La description ne doit contenir que du texte et des chiffres.' };
  }
  
  if (description.length > 500) {
    return { valid: false, message: 'La description ne doit pas dépasser 500 caractères.' };
  }
  
  return { valid: true };
}

// Fonction principale de validation du formulaire
function validateFormationForm(form) {
  let isValid = true;
  
  // Récupérer les champs
  const titreInput = form.querySelector('[name="titre"]');
  const dureeInput = form.querySelector('[name="duree"]');
  const prixInput = form.querySelector('[name="prix"]');
  const niveauInput = form.querySelector('[name="niveau"]');
  const descriptionInput = form.querySelector('[name="description"]');
  
  // Nettoyer toutes les erreurs précédentes
  [titreInput, dureeInput, prixInput, niveauInput, descriptionInput].forEach(input => {
    if (input) clearError(input);
  });
  
  // Valider le titre
  const titreResult = validateTitre(titreInput.value);
  if (!titreResult.valid) {
    showError(titreInput, titreResult.message);
    isValid = false;
  }
  
  // Valider la durée
  const dureeResult = validateDuree(dureeInput.value);
  if (!dureeResult.valid) {
    showError(dureeInput, dureeResult.message);
    isValid = false;
  }
  
  // Valider le prix
  const prixResult = validatePrix(prixInput.value);
  if (!prixResult.valid) {
    showError(prixInput, prixResult.message);
    isValid = false;
  }
  
  // Valider le niveau
  const niveauResult = validateNiveau(niveauInput.value);
  if (!niveauResult.valid) {
    showError(niveauInput, niveauResult.message);
    isValid = false;
  }
  
  // Valider la description
  const descriptionResult = validateDescription(descriptionInput.value);
  if (!descriptionResult.valid) {
    showError(descriptionInput, descriptionResult.message);
    isValid = false;
  }
  
  return isValid;
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
  
  // Validation en temps réel pour le formulaire d'ajout
  const addForm = document.querySelector('#addFormationModal form');
  if (addForm) {
    // Supprimer les attributs HTML5
    addForm.querySelectorAll('[required]').forEach(input => input.removeAttribute('required'));
    addForm.querySelectorAll('[min]').forEach(input => input.removeAttribute('min'));
    addForm.querySelectorAll('[max]').forEach(input => input.removeAttribute('max'));
    addForm.querySelectorAll('[step]').forEach(input => input.removeAttribute('step'));
    
    // Ajouter la validation à la soumission
    addForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (validateFormationForm(this)) {
        // Si la validation passe, soumettre le formulaire
        this.submit();
      }
    });
    
    // Validation en temps réel sur les champs
    const titreInput = addForm.querySelector('[name="titre"]');
    const dureeInput = addForm.querySelector('[name="duree"]');
    const prixInput = addForm.querySelector('[name="prix"]');
    const niveauInput = addForm.querySelector('[name="niveau"]');
    const descriptionInput = addForm.querySelector('[name="description"]');
    
    if (titreInput) {
      titreInput.addEventListener('blur', function() {
        const result = validateTitre(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (dureeInput) {
      dureeInput.addEventListener('blur', function() {
        const result = validateDuree(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (prixInput) {
      prixInput.addEventListener('blur', function() {
        const result = validatePrix(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (niveauInput) {
      niveauInput.addEventListener('change', function() {
        const result = validateNiveau(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (descriptionInput) {
      descriptionInput.addEventListener('blur', function() {
        const result = validateDescription(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
  }
  
  // Validation pour le formulaire de modification
  const editForm = document.querySelector('#editFormationModal form');
  if (editForm) {
    // Supprimer les attributs HTML5
    editForm.querySelectorAll('[required]').forEach(input => input.removeAttribute('required'));
    editForm.querySelectorAll('[min]').forEach(input => input.removeAttribute('min'));
    editForm.querySelectorAll('[max]').forEach(input => input.removeAttribute('max'));
    editForm.querySelectorAll('[step]').forEach(input => input.removeAttribute('step'));
    
    // Ajouter la validation à la soumission
    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (validateFormationForm(this)) {
        // Si la validation passe, soumettre le formulaire
        this.submit();
      }
    });
    
    // Validation en temps réel sur les champs
    const editTitreInput = editForm.querySelector('[name="titre"]');
    const editDureeInput = editForm.querySelector('[name="duree"]');
    const editPrixInput = editForm.querySelector('[name="prix"]');
    const editNiveauInput = editForm.querySelector('[name="niveau"]');
    const editDescriptionInput = editForm.querySelector('[name="description"]');
    
    if (editTitreInput) {
      editTitreInput.addEventListener('blur', function() {
        const result = validateTitre(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (editDureeInput) {
      editDureeInput.addEventListener('blur', function() {
        const result = validateDuree(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (editPrixInput) {
      editPrixInput.addEventListener('blur', function() {
        const result = validatePrix(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (editNiveauInput) {
      editNiveauInput.addEventListener('change', function() {
        const result = validateNiveau(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
    
    if (editDescriptionInput) {
      editDescriptionInput.addEventListener('blur', function() {
        const result = validateDescription(this.value);
        if (!result.valid) {
          showError(this, result.message);
        } else {
          clearError(this);
        }
      });
    }
  }
});
