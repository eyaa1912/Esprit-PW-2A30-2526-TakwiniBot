# TakwiniBot Advanced Avatar System - Setup Guide

## 📋 Overview

This guide explains how to integrate the new advanced avatar system into your existing `create.php` and `edit.php` files WITHOUT modifying them.

---

## 🎯 New Files Created

### 1. **Controller/AvatarLangController.php**
- OOP class with strict type declarations
- Manages language switching (FR/EN/AR)
- Handles accessibility settings
- Provides translations and sign language cards

### 2. **css/takwini_avatar.css**
- Floating avatar panel styling
- Animated SVG face (blinking eyes, talking mouth)
- Sign language cards grid
- Accessibility toggles
- Subtitle bar
- Responsive design
- Accessibility features (high contrast, reduced motion)

### 3. **View/entretien/_avatar_assistant_advanced.php**
- Avatar UI component
- Language switcher buttons
- Sign language cards (6 cards with emojis)
- Accessibility toggles (TTS, Subtitles, Sign Panel)
- Web Speech API integration
- Field hints with data-hint attributes

---

## 🚀 How to Integrate

### Step 1: Add CSS Link to create.php and edit.php

In the `<head>` section, add:
```html
<link rel="stylesheet" href="css/takwini_avatar.css">
```

### Step 2: Include Avatar Partial

Before the closing `</body>` tag, add:
```php
<?php include 'View/entretien/_avatar_assistant_advanced.php'; ?>
```

### Step 3: Add data-hint Attributes to Form Fields

Add `data-hint` attributes to your form inputs:

```html
<input type="text" id="nom_candidat" name="nom_candidat" 
       class="form-control" 
       data-hint="Veuillez entrer votre nom complet">

<input type="email" id="email_candidat" name="email_candidat" 
       class="form-control" 
       data-hint="Entrez votre adresse email valide">

<select id="genre" name="genre" class="form-control"
        data-hint="Sélectionnez votre genre">
    <option value="homme">Homme</option>
    <option value="femme">Femme</option>
</select>

<input type="text" id="type_handicap" name="type_handicap" 
       class="form-control" 
       data-hint="Précisez votre type de handicap">

<input type="text" id="amenagements" name="amenagements" 
       class="form-control" 
       data-hint="Décrivez les aménagements nécessaires">

<select id="type_entretien_id" name="type_entretien_id" class="form-control"
        data-hint="Choisissez le type d'entretien">
    <!-- options -->
</select>

<input type="date" id="date_entretien" name="date_entretien" 
       class="form-control" 
       data-hint="Sélectionnez la date de l'entretien">

<input type="time" id="heure_entretien" name="heure_entretien" 
       class="form-control" 
       data-hint="Choisissez l'heure de l'entretien">

<input type="text" id="poste_cible" name="poste_cible" 
       class="form-control" 
       data-hint="Entrez le poste cible">

<select id="statut" name="statut" class="form-control"
        data-hint="Sélectionnez le statut de l'entretien">
    <!-- options -->
</select>

<textarea id="remarques" name="remarques" class="form-control"
          data-hint="Ajoutez vos remarques supplémentaires"></textarea>
```

---

## 🎨 Features

### 1. **Floating Avatar Panel** (Bottom-Right)
- Animated SVG face with blinking eyes
- Gradient background (purple to violet)
- Smooth slide-in animation
- Fixed position on screen

### 2. **Language Switcher**
- 3 buttons: FR, EN, AR
- Saves selection in `$_SESSION['avatar_lang']`
- Reloads page with new language
- Active button highlighted

### 3. **Sign Language Cards**
- 6 cards with emojis:
  - 👋 Bonjour / Hello / مرحبا
  - 👍 Oui / Yes / نعم
  - ❓ Question / Question / سؤال
  - 💪 Courage / Courage / شجاعة
  - 🙏 Merci / Thank you / شكرا
  - ⏸️ Pause / Pause / توقف
- Clickable cards trigger TTS and subtitles
- Collapsible panel

### 4. **Accessibility Toggles**
- **TTS (Text-to-Speech)**: Enable/disable voice reading
- **Subtitles**: Show/hide text at bottom of screen
- **Sign Panel**: Show/hide sign language cards
- Settings saved in `$_SESSION`

### 5. **Web Speech API Integration**
- Reads field hints aloud on focus
- Reads field hints when clicking hint icon (🔊)
- Supports FR, EN, AR languages
- Smooth subtitle display

### 6. **Subtitle Bar**
- Fixed at bottom of screen
- Shows when speaking
- Auto-hides after 3 seconds
- Dark background with white text

---

## 📝 Example Integration in create.php

```php
<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';

// ... existing code ...

$assets = '/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/assets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <!-- ... existing meta tags ... -->
    <link rel="stylesheet" href="<?= $assets ?>/bootstrap/css/bootstrap.min.css">
    <!-- ... other stylesheets ... -->
    <link rel="stylesheet" href="css/takwini_avatar.css">
</head>
<body>
    <!-- ... existing header and form ... -->
    
    <form method="post" action="create.php">
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="nom_candidat">Nom candidat *</label>
                <input type="text" id="nom_candidat" name="nom_candidat" 
                       class="form-control" 
                       data-hint="Veuillez entrer votre nom complet"
                       value="<?= e($data['nom_candidat']) ?>">
            </div>
            <!-- ... more fields with data-hint ... -->
        </div>
    </form>
    
    <!-- ... existing footer ... -->
    
    <script src="<?= $assets ?>/js/jquery-1.12.4.min.js"></script>
    <!-- ... other scripts ... -->
    
    <?php include 'View/entretien/_avatar_assistant_advanced.php'; ?>
</body>
</html>
```

---

## 🔧 Customization

### Change Avatar Colors
Edit `css/takwini_avatar.css`:
```css
.avatar-panel {
    background: linear-gradient(135deg, #YOUR_COLOR1 0%, #YOUR_COLOR2 100%);
}
```

### Add More Languages
Edit `Controller/AvatarLangController.php`:
```php
private array $supportedLangs = ['fr', 'en', 'ar', 'es']; // Add 'es'

// Add translations in initializeTranslations()
'es' => [
    'hello' => 'Hola',
    // ... more translations
]
```

### Customize Sign Cards
Edit `Controller/AvatarLangController.php`:
```php
public function getSignCards(): array
{
    return [
        ['id' => 'hello', 'emoji' => '👋', 'label' => $this->translate('hello')],
        // Add more cards
    ];
}
```

---

## 🎯 How It Works

### 1. **Page Load**
- Avatar panel appears in bottom-right corner
- Language defaults to FR (or from session)
- Accessibility settings loaded from session

### 2. **User Focuses on Field**
- Field hint is read aloud (if TTS enabled)
- Subtitle appears at bottom (if subtitles enabled)
- Hint icon (🔊) appears on field

### 3. **User Clicks Hint Icon**
- Field hint is read aloud again
- Subtitle updates

### 4. **User Clicks Sign Card**
- Sign text is read aloud (if TTS enabled)
- Subtitle appears (if subtitles enabled)

### 5. **User Switches Language**
- Page reloads with new language
- All text updates to new language
- Session saves preference

### 6. **User Toggles Accessibility**
- Setting is saved in session
- Avatar behavior updates immediately

---

## ✅ Constraints Maintained

✅ **No HTML5 validation attributes** - All validation is server-side PHP  
✅ **PDO only** - Uses `config::getConnexion()`  
✅ **Strict MVC + OOP** - AvatarLangController is proper OOP class  
✅ **htmlspecialchars() on all output** - All user data escaped  
✅ **No existing files modified** - Only new files added  
✅ **Session-based settings** - Uses `$_SESSION` for persistence  

---

## 🧪 Testing Checklist

- [ ] Avatar panel appears in bottom-right
- [ ] Language switcher works (FR/EN/AR)
- [ ] Sign cards display and are clickable
- [ ] TTS toggle works
- [ ] Subtitles toggle works
- [ ] Sign panel toggle works
- [ ] Field hints read on focus
- [ ] Hint icons appear on fields
- [ ] Subtitle bar appears when speaking
- [ ] Settings persist after page reload
- [ ] Responsive on mobile
- [ ] High contrast mode works
- [ ] Reduced motion respected

---

## 📱 Browser Support

- ✅ Chrome/Edge (full support)
- ✅ Firefox (full support)
- ⚠️ Safari (partial - Web Speech API limited)
- ❌ IE (not supported)

---

## 🔐 Security

- All output escaped with `htmlspecialchars()`
- No direct database access from avatar
- Session-based settings only
- No external API calls
- All JavaScript is inline (no external dependencies)

---

**Version**: 1.0  
**Date**: May 2026  
**Author**: TakwiniBot Team
