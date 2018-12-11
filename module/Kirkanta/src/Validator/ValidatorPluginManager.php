<?php

namespace Kirkanta\Validator;

use Zend\I18n\Translator\Translator;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Zend\Session\Container as SessionData;
use Zend\Validator\ValidatorPluginManager as BaseValidatorManager;
use Zend\Validator\Translator\TranslatorAwareInterface;

class ValidatorPluginManager extends BaseValidatorManager
{
    protected $translator;

    public function getTranslator()
    {
        if (!$this->translator) {
            $ui_lang = (new SessionData('kirkanta'))->ui_language;
            $translator = new Translator;
            $translator->addTranslationFilePattern('gettext', 'module/Kirkanta/gettext', '%s/zf2form.mo', 'default');
            $this->translator = new MvcTranslator($translator);

            if ($ui_lang) {
                $translator->setLocale($ui_lang);
            }
        }
        return $this->translator;
    }
}
