# Guide de Test - Avatar Assistant Amélioré

## Vue d'ensemble

L'avatar assistant a été amélioré pour **VRAIMENT** aider TOUS les types de handicap avec des adaptations spécifiques et complètes.

---

## 🎯 Tests par Type de Handicap

### 1. HANDICAP VISUEL (Malvoyant/Aveugle)

**Adaptations activées :**
- ✅ Mode haut contraste (avatar noir, bordures épaisses)
- ✅ Lecture vocale de TOUS les labels au focus
- ✅ Annonce vocale de ce qui est tapé (après 1.5s)
- ✅ Labels ARIA sur tous les champs
- ✅ Vitesse de parole réduite (0.8x)
- ✅ Navigation clavier complète

**Comment tester :**
1. Cocher "Candidat en situation de handicap"
2. Sélectionner type_handicap = "visuel"
3. Observer : Avatar devient noir, bordures épaisses
4. Cliquer sur "Activer l'assistant"
5. Utiliser Tab pour naviguer entre les champs
6. **Résultat attendu** : Chaque champ annonce son label à voix haute
7. Taper du texte dans un champ
8. **Résultat attendu** : Après 1.5s, l'avatar lit ce qui a été tapé

**Notification affichée :**
> "Mode malvoyant : Toutes les questions seront lues à voix haute. Utilisez la touche Tab pour naviguer."

---

### 2. HANDICAP MOTEUR (Mobilité réduite)

**Adaptations activées :**
- ✅ Contrôle vocal 100% (pas besoin de clavier)
- ✅ Boutons agrandis (50px de hauteur minimum)
- ✅ Commandes vocales spéciales :
  - "Enregistrer" ou "Soumettre" → Soumet le formulaire
  - "Répéter" → Répète la question
  - "Passer" → Passe à la question suivante
  - "Arrêter" ou "Stop" → Désactive l'assistant

**Comment tester :**
1. Cocher "Candidat en situation de handicap"
2. Sélectionner type_handicap = "moteur"
3. Observer : Tous les boutons deviennent plus grands
4. Cliquer sur "Activer l'assistant"
5. Répondre aux questions par la voix
6. Dire "Enregistrer" à voix haute
7. **Résultat attendu** : Le formulaire se soumet automatiquement

**Commandes vocales à tester :**
- 🎤 "Répéter" → Répète la dernière question
- 🎤 "Passer" → Saute la question actuelle
- 🎤 "Arrêter" → Désactive l'assistant
- 🎤 "Enregistrer" → Soumet le formulaire

**Notification affichée :**
> "Mode handicap moteur : Utilisez uniquement votre voix. Dites 'Enregistrer' pour soumettre le formulaire."

---

### 3. HANDICAP COGNITIF (Déficience intellectuelle)

**Adaptations activées :**
- ✅ Questions simplifiées (langage court et clair)
- ✅ Boutons TRÈS GRANDS (60px de hauteur)
- ✅ Texte agrandi partout (20-22px)
- ✅ Indicateur de progression visuel ("Étape X sur 5")
- ✅ Vitesse de parole très lente (0.7x)
- ✅ Police en gras

**Questions simplifiées :**
- "Quel est votre nom complet ?" → **"Votre nom ?"**
- "Quel est votre type de handicap ?" → **"Votre handicap ?"**
- "Quels aménagements souhaitez-vous ?" → **"Ce dont vous avez besoin au travail ?"**
- "Quel poste souhaitez-vous occuper ?" → **"Quel travail voulez-vous ?"**
- "Avez-vous des remarques ?" → **"Autre chose à dire ?"**

**Comment tester :**
1. Cocher "Candidat en situation de handicap"
2. Sélectionner type_handicap = "cognitif"
3. Observer : 
   - Tous les boutons deviennent ÉNORMES
   - Tout le texte devient plus grand et en gras
   - Un indicateur "Étape 1 sur 5" apparaît en haut
4. Cliquer sur "Activer l'assistant"
5. **Résultat attendu** : Questions courtes et simples
6. Observer l'indicateur de progression qui se met à jour

**Notification affichée :**
> "Mode simplifié : Questions courtes, gros boutons, guidage étape par étape."

---

### 4. HANDICAP AUDITIF (Sourd/Malentendant) ⭐ NOUVEAU

**Adaptations activées :**
- ✅ **AUCUN SON** (audio complètement désactivé)
- ✅ Affichage visuel du texte dans un panneau central
- ✅ Animations visuelles renforcées (bounce + glow)
- ✅ Panneau de texte avec gradient violet
- ✅ Texte affiché pendant 5 secondes
- ✅ Avatar qui rebondit et brille

**Comment tester :**
1. Cocher "Candidat en situation de handicap"
2. Sélectionner type_handicap = "auditif"
3. Observer : Avatar commence à rebondir et briller
4. Cliquer sur "Activer l'assistant"
5. **Résultat attendu** : 
   - AUCUN son ne sort
   - Un panneau violet apparaît au centre de l'écran
   - Le texte de la question s'affiche visuellement
   - Le panneau disparaît après 5 secondes
6. Répondre par la voix (reconnaissance vocale fonctionne toujours)
7. Observer : Chaque question/confirmation s'affiche visuellement

**Panneau visuel :**
- Position : Centre de l'écran
- Couleur : Gradient violet (#667eea → #764ba2)
- Taille : 24px, gras, blanc
- Animation : Pulse (agrandissement/rétrécissement)
- Durée : 5 secondes

**Notification affichée :**
> "Mode sourd/malentendant : Toutes les informations sont affichées visuellement. Pas de son."

---

## 🧪 Scénarios de Test Complets

### Scénario 1 : Candidat malvoyant
```
1. Ouvrir add.php
2. Cocher "Candidat en situation de handicap"
3. Type handicap = "visuel"
4. Activer l'assistant
5. Utiliser Tab pour naviguer
6. Vérifier que chaque champ est lu à voix haute
7. Taper du texte et vérifier la lecture
```

### Scénario 2 : Candidat en fauteuil roulant (moteur)
```
1. Ouvrir add.php
2. Cocher "Candidat en situation de handicap"
3. Type handicap = "moteur"
4. Activer l'assistant
5. Répondre UNIQUEMENT par la voix
6. Dire "Enregistrer" pour soumettre
7. Vérifier que le formulaire se soumet
```

### Scénario 3 : Candidat avec déficience intellectuelle
```
1. Ouvrir add.php
2. Cocher "Candidat en situation de handicap"
3. Type handicap = "cognitif"
4. Observer les GROS boutons et texte
5. Activer l'assistant
6. Vérifier les questions simplifiées
7. Observer l'indicateur "Étape X sur 5"
```

### Scénario 4 : Candidat sourd
```
1. Ouvrir add.php
2. Cocher "Candidat en situation de handicap"
3. Type handicap = "auditif"
4. Activer l'assistant
5. Vérifier qu'AUCUN son ne sort
6. Observer le panneau violet au centre
7. Lire le texte affiché visuellement
8. Répondre par la voix (STT fonctionne)
```

---

## 🎨 Indicateurs Visuels

### Mode Visuel (malvoyant)
- Avatar : Noir
- Bordures : Épaisses (3px)
- Contraste : Maximum

### Mode Moteur
- Boutons : 50px de hauteur
- Padding : 15px 30px
- Font : 18px

### Mode Cognitif
- Boutons : 60px de hauteur
- Padding : 20px 40px
- Font : 22px, gras
- Labels : 22px, gras
- Inputs : 20px, 50px de hauteur
- Indicateur : "Étape X sur 5" en haut

### Mode Auditif
- Avatar : Rebondit + brille
- Panneau : Gradient violet, centre écran
- Texte : 24px, gras, blanc
- Animation : Pulse

---

## 🐛 Problèmes Connus et Solutions

### Problème : L'avatar ne parle pas
**Solution :** Vérifier que le navigateur est Chrome/Edge et que le volume est activé

### Problème : La reconnaissance vocale ne fonctionne pas
**Solution :** Autoriser l'accès au microphone dans les paramètres du navigateur

### Problème : Le panneau visuel (auditif) ne s'affiche pas
**Solution :** Vérifier que type_handicap contient "auditif" (minuscules)

### Problème : Les boutons ne sont pas agrandis
**Solution :** Recharger la page après avoir sélectionné le type de handicap

---

## 📊 Checklist de Validation

### Handicap Visuel
- [ ] Avatar devient noir
- [ ] Bordures épaisses visibles
- [ ] Labels lus au focus
- [ ] Texte tapé annoncé après 1.5s
- [ ] Navigation Tab fonctionne
- [ ] Vitesse de parole lente

### Handicap Moteur
- [ ] Boutons agrandis (50px)
- [ ] Commande "Enregistrer" fonctionne
- [ ] Commande "Répéter" fonctionne
- [ ] Commande "Passer" fonctionne
- [ ] Commande "Arrêter" fonctionne
- [ ] Formulaire soumis par la voix

### Handicap Cognitif
- [ ] Boutons TRÈS grands (60px)
- [ ] Texte agrandi partout
- [ ] Questions simplifiées
- [ ] Indicateur "Étape X sur 5" visible
- [ ] Vitesse de parole très lente
- [ ] Police en gras

### Handicap Auditif
- [ ] AUCUN son ne sort
- [ ] Panneau violet apparaît
- [ ] Texte affiché au centre
- [ ] Avatar rebondit
- [ ] Avatar brille (glow)
- [ ] Panneau disparaît après 5s
- [ ] Reconnaissance vocale fonctionne

---

## 🚀 Améliorations Futures Possibles

- [ ] Langue des signes (vidéo avatar)
- [ ] Sous-titres en temps réel
- [ ] Synthèse vocale en arabe
- [ ] Mode dyslexie (police OpenDyslexic)
- [ ] Contrôle par eye-tracking
- [ ] Support des switch controls
- [ ] Mode daltonien (couleurs adaptées)

---

**Version** : 2.0 - Avatar Inclusif Complet  
**Date** : Mai 2026  
**Auteur** : TakwiniBot Team
