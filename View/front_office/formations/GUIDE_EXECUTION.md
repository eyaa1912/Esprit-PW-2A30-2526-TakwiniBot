# 🎯 Guide d'Exécution - Assistant Vocal

## ✅ Étape 1 : Tester rapidement (Sans serveur)

### Option A : Page de démonstration

1. **Ouvrir le fichier** :
   ```
   gestion_formation/View/front_office/formations/demo-assistant-vocal.html
   ```

2. **Double-cliquer** sur le fichier
   - OU clic droit → "Ouvrir avec" → Chrome/Firefox/Edge

3. **Résultat** :
   - Une belle page colorée s'affiche
   - Un bouton flottant apparaît en bas à droite (🔊)
   - Passez votre curseur sur les mots → Ils sont lus automatiquement !

### Option B : Page index existante

1. **Ouvrir le fichier** :
   ```
   gestion_formation/View/front_office/formations/index.html
   ```

2. **Double-cliquer** sur le fichier

3. **Tester** :
   - Cherchez le bouton flottant en bas à droite
   - Passez votre curseur sur les textes de la page

---

## 🖥️ Étape 2 : Avec serveur local (Recommandé)

### Si vous utilisez XAMPP :

1. **Démarrer XAMPP** :
   - Ouvrir XAMPP Control Panel
   - Cliquer sur "Start" pour Apache
   - Cliquer sur "Start" pour MySQL

2. **Ouvrir dans le navigateur** :
   ```
   http://localhost/gestion_formation/View/front_office/formations/demo-assistant-vocal.html
   ```
   
   OU pour les pages PHP :
   ```
   http://localhost/gestion_formation/View/front_office/formations/formation.php
   ```

3. **Tester** :
   - Le bouton flottant apparaît automatiquement
   - Passez votre curseur sur n'importe quel texte
   - Écoutez la magie ! 🎤

### Si vous utilisez WAMP :

1. **Démarrer WAMP** :
   - Cliquer sur l'icône WAMP
   - Attendre que l'icône devienne verte

2. **Ouvrir** :
   ```
   http://localhost/gestion_formation/View/front_office/formations/demo-assistant-vocal.html
   ```

### Si vous utilisez MAMP (Mac) :

1. **Démarrer MAMP**
2. **Ouvrir** :
   ```
   http://localhost:8888/gestion_formation/View/front_office/formations/demo-assistant-vocal.html
   ```

---

## 🎮 Comment utiliser l'assistant vocal

### 1. Activer/Désactiver

- **Bouton flottant** en bas à droite de la page
- **Icône 🔊** = Activé (couleur violette)
- **Icône 🔇** = Désactivé (couleur grise)
- **Cliquer** pour basculer entre les deux états

### 2. Écouter le texte

**Méthode 1 : Survol simple**
- Passez votre curseur sur un mot
- Le mot est lu automatiquement
- Le texte se surligne légèrement

**Méthode 2 : Survol de phrases**
- Passez votre curseur sur un paragraphe
- La phrase entière est lue

**Méthode 3 : Survol de titres**
- Passez votre curseur sur un titre (h1, h2, h3...)
- Le titre est lu

### 3. Arrêter la lecture

- Déplacez simplement votre curseur ailleurs
- OU cliquez sur le bouton pour désactiver l'assistant

---

## 🔧 Ajouter à d'autres pages

### Pour ajouter l'assistant vocal à n'importe quelle page :

1. **Ouvrir votre fichier HTML/PHP**

2. **Trouver la balise `</body>`** (à la fin du fichier)

3. **Ajouter AVANT `</body>` :**
   ```html
   <!-- Assistant Vocal -->
   <script src="assets/js/voice-assistant.js"></script>
   </body>
   ```

### Exemple complet :

```html
<!DOCTYPE html>
<html>
<head>
    <title>Ma Page</title>
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
</head>
<body>
    
    <h1>Bienvenue</h1>
    <p>Passez votre curseur sur ce texte</p>
    
    <!-- Vos autres scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Assistant Vocal -->
    <script src="assets/js/voice-assistant.js"></script>
</body>
</html>
```

---

## ✅ Pages déjà configurées

Ces pages ont déjà l'assistant vocal :

- ✅ `index.html`
- ✅ `formation.php`
- ✅ `demo-assistant-vocal.html`

---

## 🎯 Test rapide en 30 secondes

1. **Ouvrir** : `demo-assistant-vocal.html` (double-clic)
2. **Chercher** : Bouton flottant en bas à droite
3. **Passer** : Curseur sur "Bonjour"
4. **Écouter** : La voix dit "Bonjour" !

---

## ❓ Problèmes courants

### Le bouton n'apparaît pas ?

**Solution 1** : Vérifiez que Font Awesome est chargé
```html
<link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
```

**Solution 2** : Ouvrez la console (F12) et vérifiez les erreurs

**Solution 3** : Vérifiez que le fichier `voice-assistant.js` existe :
```
gestion_formation/View/front_office/formations/assets/js/voice-assistant.js
```

### Pas de son ?

**Solution 1** : Vérifiez le volume de votre ordinateur

**Solution 2** : Cliquez sur le bouton pour l'activer (🔊)

**Solution 3** : Cliquez d'abord sur la page (certains navigateurs bloquent l'audio automatique)

**Solution 4** : Essayez un autre navigateur (Chrome recommandé)

### La voix n'est pas en français ?

**Solution** : Installez des voix françaises sur votre système
- **Windows** : Paramètres → Heure et langue → Voix
- **Mac** : Préférences Système → Accessibilité → Parole

---

## 📱 Test sur mobile

1. **Transférer** les fichiers sur votre serveur web
2. **Ouvrir** depuis votre téléphone
3. **Toucher** le texte au lieu de survoler

**Note** : Sur iOS, l'audio peut nécessiter une interaction utilisateur d'abord.

---

## 🎓 Vidéo de démonstration (à créer)

Si vous voulez créer une vidéo de démonstration :

1. Ouvrir `demo-assistant-vocal.html`
2. Enregistrer l'écran avec OBS Studio ou autre
3. Montrer :
   - Le bouton flottant
   - Le survol des mots
   - L'activation/désactivation
   - L'effet visuel

---

## 📞 Besoin d'aide ?

1. Lisez `README_ASSISTANT_VOCAL.md` pour plus de détails
2. Consultez `INSTRUCTIONS_ASSISTANT_VOCAL.md`
3. Testez avec `demo-assistant-vocal.html`
4. Contactez l'équipe de développement

---

**Bon test ! 🎉**
