<?php

namespace Kirkanta\I18n\Form\Element;

use Zend\Form\Element;
use Kirkanta\I18n\TranslationsContainer;

class Translations extends Element
{
    protected $translations = [];

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        // $this->translations = new TranslationsContainer;
    }

    public function setValue($value)
    {
        /*
         * NOTE: Have to merge with old values to keep translations for those
         * fields that aren't included in the form validation group. But this
         * exposes a bug that will keep translations for fields that have
         * been dropped.
         */

        $translations = $this->getValue();
        foreach ($value as $lang => $strings) {
            $translations[$lang] = $strings + (isset($translations[$lang]) ? $translations[$lang] : []);
        }
        // $this->translations->setTranslations($translations);
        $this->translations = $translations;
    }

    public function getValue()
    {
        return $this->translations;
        return $this->translations->getTranslations();
    }

    public function getContainer()
    {
        return new TranslationsContainer($this->translations);
        return $this->translations;
    }
}
