# 🎤 Assistant Vocal - Voice to Text (Text-to-Speech)

## 📋 Description

Un assistant vocal intelligent qui lit automatiquement le texte lorsque le curseur passe sur un mot ou un élément textuel. Cette fonctionnalité améliore l'accessibilité et l'expérience utilisateur du front office.

## ✨ Fonctionnalités

- 🗣️ **Lecture automatique** : Le texte est lu dès que vous passez votre curseur dessus
- 🇫🇷 **Support français** : Utilise une voix française naturelle
- 🎛️ **Contrôle facile** : Bouton flottant pour activer/désactiver
- 👁️ **Effet visuel** : Surlignage du texte en cours de lecture
- 🎨 **Design moderne** : Bouton flottant avec gradient et animations
- 📱 **Responsive** : Fonctionne sur tous les appareils

## 🚀 Installation

### Fichiers créés

1. **`assets/js/voice-assistant.js`** - Script principal de l'assistant vocal
2. **`demo-assistant-vocal.html`** - Page de démonstration
3. **`INSTRUCTIONS_ASSISTANT_VOCAL.md`** - Instructions détaillées
4. **`README_ASSISTANT_VOCAL.md`** - Ce fichier

### Pages déjà configurées

- ✅ `index.html`
- ✅ `formation.php`

### Ajouter à d'autres pages

Ajoutez cette ligne **avant la balise `</body>`** :

```html
<!-- Assistant Vocal -->
<script src="assets/js/voice-assistant.js"></script>
```

## 🎯 Utilisation

### Pour les utilisateurs

1. **Ouvrir une page** du front office
2. **Chercher le bouton flottant** en bas à droite (icône haut-parleur 🔊)
3. **Passer le curseur** sur n'importe quel texte
4. **L'assistant lit** automatiquement le contenu
5. **Cliquer sur le bouton** pour activer/désactiver

### Tester la fonctionnalité

Ouvrez le fichier de démonstration :
```
gestion_formation/View/front_office/formations/demo-assistant-vocal.html
```

## ⚙️ Configuration

### Modifier la vitesse de lecture

Dans `voice-assistant.js`, ligne 145 :

```javascript
this.currentUtterance.rate = 1.0; 
// 0.5 = lent
// 1.0 = normal (défaut)
// 1.5 = rapide
// 2.0 = très rapide
```

### Modifier le volume

Dans `voice-assistant.js`, ligne 147 :

```javascript
this.currentUtterance.volume = 1.0; 
// 0.0 = muet
// 0.5 = moyen
// 1.0 = maximum (défaut)
```

### Modifier la tonalité

Dans `voice-assistant.js`, ligne 146 :

```javascript
this.currentUtterance.pitch = 1.0; 
// 0.5 = grave
// 1.0 = normal (défaut)
// 2.0 = aigu
```

### Changer la position du bouton

Dans `voice-assistant.js`, lignes 42-43 :

```javascript
bottom: 20px;  // Distance du bas (en pixels)
right: 20px;   // Distance de la droite (en pixels)
```

Exemples :
- En haut à droite : `top: 20px; right: 20px;`
- En bas à gauche : `bottom: 20px; left: 20px;`
- En haut à gauche : `top: 20px; left: 20px;`

### Personnaliser les couleurs

Dans `voice-assistant.js`, ligne 47 :

```javascript
// Bouton activé (gradient violet/bleu)
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

// Bouton désactivé (gris)
button.style.background = '#6c757d';
```

## 🎨 Personnalisation avancée

### Changer l'effet de survol

Dans `voice-assistant.js`, ligne 91 :

```javascript
element.style.backgroundColor = 'rgba(102, 126, 234, 0.1)';
// Modifier la couleur et l'opacité
```

### Ajouter un délai avant la lecture

Ajoutez un setTimeout dans la fonction `speak()` :

```javascript
speak(text) {
    setTimeout(() => {
        // Code de lecture existant
        this.synth.speak(this.currentUtterance);
    }, 300); // Délai de 300ms
}
```

## 🌐 Compatibilité navigateurs

| Navigateur | Support | Notes |
|------------|---------|-------|
| Chrome     | ✅ Excellent | Meilleur support |
| Edge       | ✅ Excellent | Basé sur Chromium |
| Firefox    | ✅ Bon | Quelques voix limitées |
| Safari     | ✅ Bon | Support iOS/macOS |
| Opera      | ✅ Excellent | Basé sur Chromium |
| IE 11      | ❌ Non supporté | API non disponible |

## 🔧 Dépannage

### Le son ne fonctionne pas

**Problème** : Aucun son n'est émis
**Solutions** :
1. Vérifiez que le volume de l'ordinateur n'est pas coupé
2. Vérifiez que le bouton de l'assistant est activé (icône 🔊)
3. Certains navigateurs bloquent l'audio automatique - cliquez d'abord sur la page
4. Ouvrez la console (F12) pour voir les erreurs

### La voix n'est pas en français

**Problème** : La voix parle dans une autre langue
**Solutions** :
1. Vérifiez les paramètres de langue de votre navigateur
2. Installez des voix françaises sur votre système :
   - **Windows** : Paramètres > Heure et langue > Voix
   - **Mac** : Préférences Système > Accessibilité > Parole
   - **Linux** : Installez `espeak` ou `festival`

### Le bouton n'apparaît pas

**Problème** : Le bouton flottant est invisible
**Solutions** :
1. Vérifiez que Font Awesome est chargé :
   ```html
   <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
   ```
2. Vérifiez la console (F12) pour les erreurs JavaScript
3. Assurez-vous que le script est chargé après le DOM

### Le texte n'est pas lu au survol

**Problème** : Rien ne se passe au survol
**Solutions** :
1. Vérifiez que l'assistant est activé (bouton avec icône 🔊)
2. Vérifiez que l'élément contient du texte
3. Essayez de cliquer sur le texte au lieu de survoler

## 📊 Performance

- **Taille du fichier** : ~6 KB (non compressé)
- **Impact sur le chargement** : Minimal
- **Utilisation mémoire** : Faible (~2-5 MB)
- **Compatible mobile** : Oui (avec limitations sur iOS)

## 🔒 Sécurité et confidentialité

- ✅ **Aucune donnée envoyée** : Tout fonctionne localement dans le navigateur
- ✅ **Pas de tracking** : Aucune collecte de données
- ✅ **API native** : Utilise l'API Web Speech du navigateur
- ✅ **Pas de dépendances externes** : Aucune bibliothèque tierce

## 🎓 Cas d'usage

### Accessibilité
- Aide les personnes malvoyantes
- Facilite la lecture pour les dyslexiques
- Support pour les personnes âgées

### Éducation
- Améliore la compréhension
- Aide à la mémorisation
- Pratique de la prononciation

### Confort
- Réduit la fatigue oculaire
- Permet le multitâche
- Navigation mains libres

## 📝 Notes techniques

### API utilisée
- **Web Speech API** : `window.speechSynthesis`
- **SpeechSynthesisUtterance** : Pour créer les énoncés
- **Événements DOM** : `mouseenter`, `mouseleave`

### Structure du code
```
VoiceAssistant (classe)
├── constructor()          // Initialisation
├── loadVoices()          // Chargement des voix
├── init()                // Configuration
├── createControlButton() // Création du bouton
├── attachListeners()     // Écouteurs d'événements
├── wrapWordsInSpans()    // Traitement des mots
├── speak(text)           // Lecture du texte
└── stop()                // Arrêt de la lecture
```

## 🚀 Améliorations futures possibles

- [ ] Sélection de la voix par l'utilisateur
- [ ] Contrôle de la vitesse via interface
- [ ] Raccourcis clavier
- [ ] Mode de lecture continue
- [ ] Sauvegarde des préférences
- [ ] Support multilingue automatique
- [ ] Synthèse vocale améliorée avec IA

## 📞 Support

Pour toute question ou problème :
1. Consultez ce README
2. Vérifiez `INSTRUCTIONS_ASSISTANT_VOCAL.md`
3. Testez avec `demo-assistant-vocal.html`
4. Contactez l'équipe de développement

## 📄 Licence

Ce code est fourni pour le projet Takwinibot.

---

**Créé avec ❤️ pour améliorer l'accessibilité web**
