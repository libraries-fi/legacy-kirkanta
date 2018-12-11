<?php

namespace Kirkanta\Form;

use Zend\I18n\Translator\TranslatorAwareTrait;

trait TranslatorAwareFieldsetTrait
{
    use TranslatorAwareTrait;

    public function tr($string)
    {
        return $this->getTranslator()->translate($string);
    }
}
