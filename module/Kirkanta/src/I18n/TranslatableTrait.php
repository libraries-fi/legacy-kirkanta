<?php

namespace Kirkanta\I18n;

trait TranslatableTrait
{
    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations(array $data)
    {
        $this->translations = empty($data) ? null : $data;
    }

    public function getTranslation($locale)
    {
        if (isset($this->translations[$locale])) {
            return $this->translations[$locale];
        }
    }

    public function setTranslation($locale, array $data)
    {
        $this->translations[$locale] = $data;
    }

    public function getLocales()
    {
        $locales = array_keys($this->translations);
        return sort($locales);
    }
}
