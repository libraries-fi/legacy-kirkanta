<?php

namespace Kirkanta\I18n\Form;

interface TranslatableFormInterface
{
    public function getTranslationsContainer();
    public function getActiveLocale();
    public function getTranslationMessages();
}
