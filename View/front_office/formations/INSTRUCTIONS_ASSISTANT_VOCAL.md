# Instructions pour l'Assistant Vocal

## Fonctionnalités

L'assistant vocal lit automatiquement le texte lorsque le curseur passe sur un mot ou un élément textuel.

### Caractéristiques :
- ✅ Lecture automatique au survol (hover)
- ✅ Bouton flottant pour activer/désactiver
- ✅ Support de la langue française
- ✅ Effet visuel au survol
- ✅ Compatible avec tous les navigateurs modernes

## Installation

### Étape 1 : Ajouter le script dans vos pages HTML/PHP

Ajoutez cette ligne **avant la balise `</body>`** dans toutes vos pages :

```html
<!-- Assistant Vocal -->
<script src="assets/js/voice-assistant.js"></script>
```

### Étape 2 : Vérifier Font Awesome

Assurez-vous que Font Awesome est chargé dans votre page (pour les icônes) :

```html
<link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
```

## Pages à modifier

Ajoutez le script dans ces fichiers :

1. ✅ `index.html` - **FAIT**
2. ⏳ `formation.php`
3. ⏳ `formation.html`
4. ⏳ `formation-details.php`
5. ⏳ `formation-details.html`
6. ⏳ `about.html`
7. ⏳ `front_formulaire_reclamation.html`
8. ⏳ `front_mes_reclamations.html`

## Utilisation

1. **Ouvrir une page** du front office
2. **Un bouton flottant** apparaît en bas à droite (icône haut-parleur)
3. **Passer le curseur** sur n'importe quel texte
4. **L'assistant lit** automatiquement le texte
5. **Cliquer sur le bouton** pour activer/désactiver

## Personnalisation

### Modifier la vitesse de lecture

Dans `voice-assistant.js`, ligne ~145 :

```javascript
this.currentUtterance.rate = 1.0; // 0.5 = lent, 1.0 = normal, 2.0 = rapide
```

### Modifier la position du bouton

Dans `voice-assistant.js`, ligne ~42-43 :

```javascript
bottom: 20px;  // Distance du bas
right: 20px;   // Distance de la droite
```

### Changer la couleur du bouton

Dans `voice-assistant.js`, ligne ~47 :

```javascript
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

## Compatibilité

- ✅ Chrome / Edge
- ✅ Firefox
- ✅ Safari
- ✅ Opera
- ❌ Internet Explorer (non supporté)

## Dépannage

### Le son ne fonctionne pas ?
- Vérifiez que le volume de votre ordinateur n'est pas coupé
- Vérifiez que le bouton de l'assistant est activé (icône haut-parleur)
- Certains navigateurs nécessitent une interaction utilisateur avant de jouer du son

### La voix n'est pas en français ?
- Le script sélectionne automatiquement une voix française si disponible
- Vérifiez les paramètres de langue de votre navigateur

### Le bouton n'apparaît pas ?
- Vérifiez que Font Awesome est bien chargé
- Ouvrez la console du navigateur (F12) pour voir les erreurs

## Support

Pour toute question ou problème, contactez l'équipe de développement.
