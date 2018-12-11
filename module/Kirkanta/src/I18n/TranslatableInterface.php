<?php

namespace Kirkanta\I18n;

interface TranslatableInterface
{
    public function getTranslations();
    public function setTranslations(array $data);

    public function getTranslation($locale);
    public function setTranslation($locale, array $data);

    public function getLocales();
}
