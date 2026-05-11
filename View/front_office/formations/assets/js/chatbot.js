/**
 * Chatbot Takwinibot — Design style violet moderne + Upload fichiers
 */
class TakwiniChatbot {
    constructor() {
        this.isOpen      = false;
        this.base        = this._resolveBase();
        this._pendingFile = null;
        this._buildKB();
        this._injectStyles();
        this._createUI();
        setTimeout(() => this._botMsg('👋 Bonjour ! Je suis l\'assistant <strong>Takwinibot</strong>.<br>Comment puis-je vous aider ?'), 600);
    }

    _resolveBase() {
        const p = window.location.pathname;
        const i = p.indexOf('/formations/');
        return i !== -1 ? window.location.origin + p.slice(0, i + '/formations/'.length)
                        : window.location.href.slice(0, window.location.href.lastIndexOf('/') + 1);
    }

    /* ── Base de connaissances ─────────────────────────────────────────── */
    _buildKB() {
        const b = this.base;
        this.kb = [
            { p:['bonjour','salut','bonsoir','hello','coucou','hi'],
              r:'👋 Bonjour ! Je suis l\'assistant Takwinibot.<br>Posez-moi vos questions sur nos <strong>formations</strong>, <strong>offres d\'emploi</strong>, <strong>réclamations</strong> ou notre équipe !' },
            { p:['c\'est quoi','qu\'est-ce que','présente','qui es-tu','à quoi sert','présentation','takwinibot'],
              r:'🌟 <strong>Takwinibot</strong> est une plateforme tunisienne dédiée à l\'emploi des personnes en situation de handicap.<br><br>Elle connecte les candidats aux entreprises inclusives grâce à :<br>• 🤖 Matching IA adapté<br>• 📄 Coaching CV automatique<br>• ♿ Critères d\'accessibilité' },
            { p:['formation','formations','voir formation','liste formation'],
              r:'📚 Nos formations professionnelles certifiantes :<br><br><a href="'+b+'formation.php">→ Voir toutes les formations</a><br><br>Chaque formation indique le niveau, le prix et le mode (en ligne / présentiel / hybride).' },
            { p:['inscription','inscrire','rejoindre','comment s\'inscrire'],
              r:'✍️ Pour s\'inscrire à une formation :<br><br>1. Allez sur <a href="'+b+'formation.php">Formations</a><br>2. Choisissez votre formation<br>3. Cliquez sur <strong>Inscription</strong><br>4. Remplissez : ID utilisateur, nom, prénom, email, niveau, mode<br>5. Cliquez <strong>S\'inscrire</strong>' },
            { p:['prix','tarif','coût','combien','gratuit','payant'],
              r:'💰 Nos formations ont des tarifs variés :<br>• Certaines sont <strong>gratuites</strong><br>• D\'autres sont <strong>payantes</strong> (prix affiché sur chaque carte)<br><br>Consultez <a href="'+b+'formation.php">la page formations</a> pour les détails.' },
            { p:['niveau','débutant','intermédiaire','avancé','expert'],
              r:'🎓 Niveaux disponibles :<br>• 🟢 <strong>Débutant</strong> — aucun prérequis<br>• 🔵 <strong>Intermédiaire</strong> — bases requises<br>• 🟠 <strong>Avancé</strong> — expérience nécessaire<br>• 🔴 <strong>Expert</strong> — niveau professionnel' },
            { p:['mode','en ligne','présentiel','hybride','distanciel'],
              r:'🖥️ Modes de formation :<br>• 💻 <strong>En ligne</strong> — depuis chez vous<br>• 🏫 <strong>Présentiel</strong> — dans nos locaux<br>• 🔀 <strong>Hybride</strong> — combinaison des deux' },
            { p:['offre','emploi','travail','postuler','candidature'],
              r:'💼 Offres d\'emploi inclusives :<br><br><a href="'+b+'offres-emploi/offres-emploi.html">→ Voir les offres d\'emploi</a><br><br>Offres sélectionnées pour les personnes en situation de handicap avec critères d\'accessibilité.' },
            { p:['réclamation','problème','signaler','plainte','déposer'],
              r:'📝 Déposer une réclamation :<br><br><a href="'+b+'front_formulaire_reclamation.html">→ Formulaire de réclamation</a><br><br>Types : problème technique, offre inappropriée, compte, suggestion.<br>📧 support@takwini.tn | 📞 +216 00 000 000' },
            { p:['mes réclamations','suivre','statut réclamation'],
              r:'📋 <a href="'+b+'front_mes_reclamations.html">→ Voir mes réclamations</a>' },
            { p:['connexion','connecter','login','compte','mot de passe'],
              r:'🔐 <a href="'+b+'Modern-Login-master/login.html">→ Se connecter</a><br><br>Connectez-vous avec votre email et mot de passe, ou créez un nouveau compte.' },
            { p:['équipe','fondateur','team','oumayma','amen','eya','yoser','fedi','slim'],
              r:'👥 Notre équipe :<br>🌟 <strong>Oumayma Dhahri</strong> — Co-Fondatrice<br>🌟 <strong>Amen Ourak</strong> — Co-Fondateur<br>🌟 <strong>Eya Toumi</strong> — Co-Fondatrice<br>🌟 <strong>Yoser Jeribi</strong> — Co-Fondatrice<br>👨‍💻 <strong>Fedi Medini</strong> — Membre<br>👨‍💻 <strong>Slim Housmi</strong> — Membre' },
            { p:['handicap','accessibilité','inclusion','inclusif','rse','aménagement'],
              r:'♿ Takwinibot est conçu pour les personnes en situation de handicap :<br>• 🤖 Matching IA adapté<br>• 📍 Accessibilité du lieu de travail<br>• 🏠 Options télétravail<br>• 🔧 Aménagements de poste<br>• 📊 Score RSE des entreprises' },
            { p:['contact','adresse','téléphone','joindre','support'],
              r:'📞 Contactez-nous :<br>📧 <strong>support@takwini.tn</strong><br>📞 <strong>+216 00 000 000</strong><br><br><a href="'+b+'front_formulaire_reclamation.html">→ Formulaire de contact</a>' },
            { p:['accueil','home','page principale'],
              r:'🏠 <a href="'+b+'index.html">→ Aller à l\'accueil</a>' },
            { p:['à propos','about','qui sommes nous'],
              r:'ℹ️ <a href="'+b+'about.html">→ Page À propos</a><br>Découvrez notre mission et notre équipe.' },
            { p:['merci','super','parfait','excellent','bravo'],
              r:'😊 Avec plaisir ! N\'hésitez pas si vous avez d\'autres questions.' },
            { p:['au revoir','bye','à bientôt'],
              r:'👋 Au revoir ! Bonne continuation sur Takwinibot 🌟' },
            { p:['aide','help','que peux-tu','fonctionnalité','comment utiliser'],
              r:'🤖 Je peux vous aider avec :<br>📚 <strong>Formations</strong> — liste, inscription, prix<br>💼 <strong>Offres d\'emploi</strong><br>📝 <strong>Réclamations</strong><br>🔐 <strong>Connexion / Compte</strong><br>👥 <strong>Équipe</strong><br>♿ <strong>Accessibilité</strong><br>📎 <strong>Pièces jointes</strong> — envoyez une image ou un document<br><br>Posez votre question !' },
            { p:['envoyer fichier','joindre','pièce jointe','document','image','photo','cv','upload','télécharger','attacher'],
              r:'📎 Pour joindre un fichier :<br><br>1. Cliquez sur le bouton <strong>📎</strong> dans la barre de saisie<br>2. Sélectionnez votre fichier<br>3. Ajoutez un message si besoin<br>4. Cliquez <strong>Envoyer</strong><br><br>Formats : <strong>JPG, PNG, GIF, WEBP, PDF, DOC, DOCX</strong> — max <strong>5 Mo</strong><br>Vous pouvez aussi <strong>glisser-déposer</strong> un fichier dans le chat !' },
        ];
    }

    _getResponse(input) {
        const t = input.toLowerCase().trim();
        for (const e of this.kb)
            for (const p of e.p)
                if (t.includes(p)) return e.r;
        return '🤔 Je n\'ai pas compris. Essayez :<br>• <em>"Quelles formations ?"</em><br>• <em>"Comment s\'inscrire ?"</em><br>• <em>"Offres d\'emploi"</em><br>• <em>"Réclamation"</em><br><br>Tapez <strong>aide</strong> pour tout voir.';
    }
