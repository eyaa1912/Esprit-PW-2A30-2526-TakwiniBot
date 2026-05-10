# TakwiniBot 3D Avatar Assistant

## Overview
The Avatar Assistant is an advanced accessibility feature designed to help disabled candidates fill out interview forms using voice commands. It combines Text-to-Speech (TTS) and Speech-to-Text (STT) technologies with an animated 3D avatar interface.

## Features

### 1. **Animated 3D Avatar**
- SVG-based animated character
- Smooth floating animation
- Blinking eyes
- Sound wave visualization when speaking
- Responsive design

### 2. **Text-to-Speech (TTS)**
- Reads form questions aloud
- Adapts speech rate based on disability type:
  - **Visual impairment**: Slower, clearer speech
  - **Cognitive disability**: Much slower, simplified language
  - **Standard**: Normal speech rate
- French language support (fr-FR)

### 3. **Speech-to-Text (STT)**
- Voice input for form fields
- Real-time transcription display
- Automatic field population
- Error handling and retry logic

### 4. **Disability-Specific Adaptations**

#### Visual Impairment (type_handicap: "visuel") ⭐ ENHANCED
- High contrast mode (black avatar, thick borders)
- Screen reader compatibility with ARIA labels
- **NEW**: All labels read aloud on focus
- **NEW**: Typed text announced after 1.5s
- Slower speech rate (0.8x)
- Full keyboard navigation with Tab
- Enhanced audio feedback

#### Motor Disability (type_handicap: "moteur") ⭐ ENHANCED
- Full voice control (100% hands-free)
- No typing or mouse required
- Large clickable buttons (50px minimum height)
- **NEW**: Advanced voice commands:
  - "Enregistrer" / "Soumettre" → Submit form
  - "Répéter" → Repeat question
  - "Passer" → Skip question
  - "Arrêter" / "Stop" → Deactivate assistant

#### Hearing Impairment (type_handicap: "auditif") ⭐ COMPLETELY NEW
- **NO AUDIO** (sound completely disabled)
- **Visual text display** in central panel with purple gradient
- **Enhanced visual animations** (bouncing + glowing avatar)
- Text panel displayed for 5 seconds
- Speech recognition still active (STT works)
- 100% visual mode without audio dependency

#### Cognitive Disability (type_handicap: "cognitif") ⭐ ENHANCED
- Simplified language with short, clear questions
- Much slower speech (0.7x rate)
- **NEW**: VERY LARGE buttons (60px height)
- **NEW**: Enlarged text everywhere (20-22px, bold)
- **NEW**: Visual progress indicator ("Step X of 5")
- **NEW**: Simplified questions:
  - "Votre nom ?" instead of "Quel est votre nom complet ?"
  - "Votre handicap ?" instead of "Quel est votre type de handicap ?"
  - "Ce dont vous avez besoin au travail ?" instead of "Quels aménagements..."
- Step-by-step guidance
- Clear visual cues

### 5. **Custom Notification System**

**NEW in v2.0**: All browser alerts replaced with elegant custom notifications

- **Notification Types**:
  - **Info** (blue): Help and settings information
  - **Success** (green): Confirmations
  - **Error** (red): Error messages and warnings

- **Features**:
  - Smooth slide-in animation from right
  - Font Awesome icons
  - Close button
  - Auto-dismiss after 5 seconds (configurable)
  - HTML content support for rich formatting
  - Mobile responsive
  - Non-blocking (doesn't interrupt user flow)

- **Usage Examples**:
  ```javascript
  // Show info notification
  this.showNotification('Message', 'info', 5000);
  
  // Show error notification
  this.showNotification('Error occurred', 'error', 8000);
  
  // Show success notification
  this.showNotification('Success!', 'success', 3000);
  ```

### 6. **Form Field Mapping**
The avatar automatically fills these fields via voice:
- `nom_candidat` - Candidate name
- `type_handicap` - Disability type
- `amenagements` - Workplace accommodations
- `poste_cible` - Target position
- `remarques` - Additional remarks

## Technical Implementation

### Architecture
```
View/
├── entretien/
│   ├── add.php (main form with avatar integration)
│   ├── _avatar_assistant.php (avatar HTML/CSS component)
│   ├── avatar_assistant.js (TTS/STT logic)
│   └── AVATAR_README.md (this file)
```

### Technologies Used
- **Web Speech API**: Browser-native TTS and STT
- **SVG**: Animated avatar graphics
- **CSS3**: Animations and transitions
- **Vanilla JavaScript**: No dependencies
- **PHP MVC**: Server-side validation maintained

### Browser Compatibility
- ✅ Chrome/Edge (full support)
- ✅ Safari (partial support)
- ❌ Firefox (limited Speech Recognition)

**Recommended**: Chrome or Edge for best experience

## Usage Instructions

### For Candidates

1. **Activate the Assistant**
   - Check "Candidat en situation de handicap"
   - The avatar appears in the bottom-right corner
   - Click "Activer l'assistant"

2. **Voice Interaction**
   - Listen to the avatar's questions
   - Speak your answer after the prompt
   - The avatar fills the form automatically

3. **Manual Override**
   - You can edit any field manually
   - Mix voice and keyboard input
   - Skip questions by filling fields beforehand

4. **Complete the Form**
   - Review all filled information
   - Click "Enregistrer" to submit

### For Administrators

1. **Enable/Disable Feature**
   - Avatar only appears when "Candidat en situation de handicap" is checked
   - Automatically adapts to `type_handicap` value

2. **Monitor Usage**
   - All form validation remains server-side (PHP)
   - No HTML5 validation attributes used
   - MVC architecture preserved

## Code Examples

### Activating the Avatar Programmatically
```javascript
// Access the global avatar instance
window.avatarAssistant.activate();
```

### Speaking Custom Text
```javascript
window.avatarAssistant.speak('Bonjour, bienvenue!');
```

### Filling a Field via Voice
```javascript
window.avatarAssistant.fillField('poste_cible', 'Développeur web');
```

### Adapting to Disability Type
```javascript
// Automatically called when type_handicap changes
window.avatarAssistant.adaptToHandicap();
```

## Accessibility Compliance

### WCAG 2.1 Level AA
- ✅ Keyboard navigation
- ✅ Screen reader compatible
- ✅ High contrast mode
- ✅ Resizable text
- ✅ Alternative input methods

### ARIA Attributes
- All form fields have `aria-label`
- Status updates use `aria-live`
- Buttons have descriptive `aria-label`

## Customization

### Changing Avatar Appearance
Edit `_avatar_assistant.php`:
```html
<!-- Modify SVG colors -->
<circle class="avatar-head" cx="100" cy="80" r="40" fill="#YOUR_COLOR"/>
```

### Adjusting Speech Rate
Edit `avatar_assistant.js`:
```javascript
utterance.rate = 0.9; // 0.1 to 2.0
utterance.pitch = 1;  // 0 to 2
```

### Adding New Questions
Edit `avatar_assistant.js`:
```javascript
this.questions = [
    { field: 'your_field', text: 'Your question?' },
    // ... more questions
];
```

## Troubleshooting

### Microphone Not Working
1. Check browser permissions
2. Ensure HTTPS connection (required for mic access)
3. Test microphone in system settings

### Speech Not Recognized
1. Speak clearly and slowly
2. Reduce background noise
3. Check browser compatibility
4. Try Chrome/Edge instead of Firefox

### Avatar Not Appearing
1. Ensure "Candidat en situation de handicap" is checked
2. Check browser console for errors
3. Verify JavaScript file is loaded

### No Sound
1. Check system volume
2. Verify browser audio permissions
3. Test with different browser

## Performance Considerations

- **Lightweight**: SVG avatar (~5KB)
- **No external dependencies**: Uses native Web APIs
- **Lazy loading**: Avatar only loads when needed
- **Minimal DOM manipulation**: Efficient rendering

## Security & Privacy

- **No data transmission**: All processing happens client-side
- **No recording storage**: Voice data not saved
- **Browser-native APIs**: No third-party services
- **GDPR compliant**: No personal data collection

## Future Enhancements

### Planned Features
- [ ] Multi-language support (Arabic, English)
- [ ] Voice commands ("Répéter", "Passer", "Arrêter")
- [ ] Customizable avatar appearance
- [ ] Emotion detection and response
- [ ] Integration with AI job suggestions
- [ ] Voice-activated form submission
- [ ] Offline mode with cached voices

### Advanced Adaptations
- [ ] Sign language avatar (for hearing impaired)
- [ ] Braille display integration
- [ ] Eye-tracking support
- [ ] Switch control compatibility

## Support

For issues or questions:
1. Check browser console for errors
2. Review this documentation
3. Test in Chrome/Edge
4. Contact development team

## License

Part of TakwiniBot project - All rights reserved © 2026

---

**Version**: 3.0.0  
**Last Updated**: May 2026  
**Author**: TakwiniBot Development Team

## Changelog

### Version 3.0.0 (May 2026) - TRULY INCLUSIVE AVATAR
- ⭐ **HEARING IMPAIRMENT MODE**: Complete visual mode with NO audio
  - Visual text panel with purple gradient
  - Enhanced animations (bounce + glow)
  - Text displayed for 5 seconds
  - 100% visual without audio dependency
- ⭐ **COGNITIVE IMPAIRMENT ENHANCEMENTS**:
  - VERY LARGE buttons (60px)
  - Enlarged text everywhere (20-22px, bold)
  - Visual progress indicator ("Step X of 5")
  - Simplified questions (short and clear)
- ⭐ **MOTOR IMPAIRMENT ENHANCEMENTS**:
  - Advanced voice commands (Submit, Repeat, Skip, Stop)
  - Larger buttons (50px)
  - 100% hands-free operation
- ⭐ **VISUAL IMPAIRMENT ENHANCEMENTS**:
  - All labels read aloud on focus
  - Typed text announced after 1.5s
  - Full keyboard navigation
  - High contrast mode with thick borders

### Version 2.0.0 (May 2026)
- ✅ **Removed all browser alerts**: Replaced `alert()` and `prompt()` with custom notifications
- ✅ **Custom notification system**: Beautiful, non-blocking notifications with animations
- ✅ **Enhanced help system**: Rich HTML content in help and settings notifications
- ✅ **Improved UX**: No more blocking browser dialogs
- ✅ **Better accessibility**: Notifications are screen-reader friendly

### Version 1.0.0 (May 2026)
- Initial release with TTS/STT functionality
- Animated SVG avatar
- Disability-specific adaptations
- Voice-controlled form filling
