<?php

namespace App\Helpers;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Exception;

class TranslationHelper
{
    /**
     * Translate text to the target language
     *
     * @param string $text
     * @param string $targetLang (default: 'en')
     * @param string|null $sourceLang (optional) If not provided, will auto-detect
     * @param bool $preserveParams Whether to preserve parameters in format :param
     * @return string|null
     */
    public static function translate(
        string $text,
        string $targetLang = 'en',
        ?string $sourceLang = null,
        bool $preserveParams = true
    ): ?string {
        try {
            $translator = new GoogleTranslate();
            
            if ($sourceLang) {
                $translator->setSource($sourceLang);
            }
            $translator->setTarget($targetLang);
            
            if ($preserveParams) {
                $translator->preserveParameters();
            }
            
            return $translator->translate($text);
        } catch (Exception $e) {
            // Log the error or handle it as needed
            \Log::error('Translation error: ' . $e->getMessage());
            return $text; // Return original text if translation fails
        }
    }
    
    /**
     * Get list of supported languages
     *
     * @param string|null $displayLang Language code to display names in (e.g., 'en')
     * @return array
     */
    public static function getSupportedLanguages(?string $displayLang = null): array
    {
        try {
            return GoogleTranslate::langs($displayLang);
        } catch (Exception $e) {
            \Log::error('Failed to get supported languages: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Detect language of the given text
     *
     * @param string $text
     * @return string|null Language code if detected, null otherwise
     */
    public static function detectLanguage(string $text): ?string
    {
        try {
            $translator = new GoogleTranslate();
            $translator->translate($text); // This will auto-detect the language
            return $translator->getLastDetectedSource();
        } catch (Exception $e) {
            \Log::error('Language detection failed: ' . $e->getMessage());
            return null;
        }
    }
}
