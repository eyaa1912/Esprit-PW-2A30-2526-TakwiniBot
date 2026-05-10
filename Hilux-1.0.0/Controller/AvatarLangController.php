<?php
declare(strict_types=1);

/**
 * AvatarLangController
 * Manages avatar language and accessibility settings
 * Strict OOP with type declarations
 */
class AvatarLangController
{
    private string $defaultLang = 'fr';
    private array $supportedLangs = ['fr', 'en', 'ar'];
    private array $translations = [];

    public function __construct()
    {
        $this->initializeTranslations();
        $this->setLanguage();
    }

    /**
     * Initialize all translations
     */
    private function initializeTranslations(): void
    {
        $this->translations = [
            'fr' => [
                'hello' => 'Bonjour',
                'yes' => 'Oui',
                'question' => 'Question',
                'courage' => 'Courage',
                'thank_you' => 'Merci',
                'pause' => 'Pause',
                'language' => 'Langue',
                'accessibility' => 'Accessibilité',
                'tts' => 'Synthèse vocale',
                'subtitles' => 'Sous-titres',
                'sign_panel' => 'Panneau de signes',
                'avatar_help' => 'Aide Avatar',
                'field_hint' => 'Indice du champ',
            ],
            'en' => [
                'hello' => 'Hello',
                'yes' => 'Yes',
                'question' => 'Question',
                'courage' => 'Courage',
                'thank_you' => 'Thank you',
                'pause' => 'Pause',
                'language' => 'Language',
                'accessibility' => 'Accessibility',
                'tts' => 'Text-to-Speech',
                'subtitles' => 'Subtitles',
                'sign_panel' => 'Sign Panel',
                'avatar_help' => 'Avatar Help',
                'field_hint' => 'Field Hint',
            ],
            'ar' => [
                'hello' => 'مرحبا',
                'yes' => 'نعم',
                'question' => 'سؤال',
                'courage' => 'شجاعة',
                'thank_you' => 'شكرا',
                'pause' => 'توقف',
                'language' => 'اللغة',
                'accessibility' => 'إمكانية الوصول',
                'tts' => 'تحويل النص إلى كلام',
                'subtitles' => 'الترجمات',
                'sign_panel' => 'لوحة الإشارات',
                'avatar_help' => 'مساعدة الصورة الرمزية',
                'field_hint' => 'تلميح الحقل',
            ],
        ];
    }

    /**
     * Set language from session or parameter
     */
    private function setLanguage(): void
    {
        if (isset($_GET['lang']) && in_array($_GET['lang'], $this->supportedLangs, true)) {
            $_SESSION['avatar_lang'] = $_GET['lang'];
        } elseif (!isset($_SESSION['avatar_lang'])) {
            $_SESSION['avatar_lang'] = $this->defaultLang;
        }
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $_SESSION['avatar_lang'] ?? $this->defaultLang;
    }

    /**
     * Get translation for a key
     */
    public function translate(string $key): string
    {
        $lang = $this->getCurrentLanguage();
        return $this->translations[$lang][$key] ?? $key;
    }

    /**
     * Get all translations for current language
     */
    public function getTranslations(): array
    {
        $lang = $this->getCurrentLanguage();
        return $this->translations[$lang] ?? $this->translations[$this->defaultLang];
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLangs;
    }

    /**
     * Get sign language cards
     */
    public function getSignCards(): array
    {
        return [
            ['id' => 'hello', 'emoji' => '👋', 'label' => $this->translate('hello')],
            ['id' => 'yes', 'emoji' => '👍', 'label' => $this->translate('yes')],
            ['id' => 'question', 'emoji' => '❓', 'label' => $this->translate('question')],
            ['id' => 'courage', 'emoji' => '💪', 'label' => $this->translate('courage')],
            ['id' => 'thank_you', 'emoji' => '🙏', 'label' => $this->translate('thank_you')],
            ['id' => 'pause', 'emoji' => '⏸️', 'label' => $this->translate('pause')],
        ];
    }

    /**
     * Get accessibility settings
     */
    public function getAccessibilitySettings(): array
    {
        return [
            'tts_enabled' => $_SESSION['avatar_tts'] ?? true,
            'subtitles_enabled' => $_SESSION['avatar_subtitles'] ?? true,
            'sign_panel_enabled' => $_SESSION['avatar_sign_panel'] ?? true,
        ];
    }

    /**
     * Toggle accessibility setting
     */
    public function toggleAccessibility(string $setting): bool
    {
        $key = 'avatar_' . $setting;
        $_SESSION[$key] = !($_SESSION[$key] ?? true);
        return $_SESSION[$key];
    }
}
