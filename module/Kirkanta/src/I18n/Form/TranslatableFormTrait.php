<?php

namespace Kirkanta\I18n\Form;

use Kirkanta\I18n\ContentLanguages;
use Kirkanta\I18n\LanguageProviderInterface;
use Kirkanta\I18n\Form\TranslationFormMessages;

trait TranslatableFormTrait
{
    protected $languageProvider;

    public function getTranslationsContainer()
    {
        if ($this->has('translations')) {
            return $this->get('translations')->getContainer();
        }
    }

    public function getActiveLocale()
    {
        if ($this->has('language')) {
            return $this->get('language')->getValue();
        }
    }

    public function getTranslationMessages()
    {
        $messages = $this->has('translations') ? $this->get('translations')->getMessages() : [];
        return new TranslationFormMessages($messages);
    }

    protected function injectTranslationsElement()
    {
        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ], ['priority' => 100]);

        // $this->translations = $this->get('translations')->getContainer();
    }

    protected function injectLanguageSelector()
    {
        $l_language = $this->getTranslator()->translate('Language');
        $l_all = $this->getTranslator()->translate('All');

        $this->add([
            'type' => 'select',
            'name' => 'language',
            'options' => [
                'label' => $l_language,
                'value_options' => ['all' => $l_all] + $this->getLanguageProvider()->getLanguages(),
            ],
        ], ['priority' => 100]);

        $this->get('language')->setValue($this->getLanguageProvider()->getDefaultLocale());
    }

    public function getLanguageProvider()
    {
        if (!$this->languageProvider) {
            $this->languageProvider = new ContentLanguages($this->getTranslator());
        }
        return $this->languageProvider;
    }

    public function setLanguageProvider(LanguageProviderInterface $provider)
    {
        $this->languageProvider = $provider;
    }
}
