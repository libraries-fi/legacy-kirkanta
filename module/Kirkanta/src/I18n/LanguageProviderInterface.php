<?php

namespace Kirkanta\I18n;

interface LanguageProviderInterface
{
    public function getLocales();
    public function getLanguages();
    public function getDefaultLocale();
}
