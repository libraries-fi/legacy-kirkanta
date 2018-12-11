<?php

namespace Kirkanta\I18n;

use Samu\Stdlib\TreeIterator;

class TranslationsContainer
{
    protected $data = [];
    protected $iterator;

    public function __construct(array $data = null)
    {
        $this->iterator = new TreeIterator;
        if (is_array($data)) {
            $this->setTranslations($data);
        }
    }

    public function getTranslation($key, $locale)
    {
        return $this->iterator->{$locale}->{$key}->value('string');
    }

    public function setTranslation($key, $locale, $value)
    {
        $this->iterator->{$locale}->{$key}->setValue($value);
    }

    public function getTranslations()
    {
        return $this->data;
    }

    public function getLocales()
    {
        return array_keys($this->data);
    }

    public function setTranslations(array $data)
    {
        $this->data = $data;
        $this->iterator = new TreeIterator($this->data);
    }
}
