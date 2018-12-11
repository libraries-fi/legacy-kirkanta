<?php

namespace Kirkanta\I18n;

use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\I18n\DummyTranslator;

class ContentLanguages implements LanguageProviderInterface
{
    protected $languages;

    public function __construct(TranslatorInterface $translator)
    {
        $this->languages = [
            'en' => $translator->translate('English'),
            'fi' => $translator->translate('Finnish'),
            'ru' => $translator->translate('Russian'),
            'se' => $translator->translate('Sami'),
            'sv' => $translator->translate('Swedish'),
        ];

        asort($this->languages);
    }

    public static function create(TranslatorInterface $translator = null)
    {
        if (!$translator) {
            $translator = new DummyTranslator;
        }
        return new static($translator);
    }

    public function getLocales()
    {
        return array_keys($this->languages);
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function getDefaultLocale()
    {
        return 'fi';
    }
}
