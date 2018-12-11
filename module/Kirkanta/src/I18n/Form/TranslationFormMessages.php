<?php

namespace Kirkanta\I18n\Form;

class TranslationFormMessages
{
    protected $messages;

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function has($field, $lang)
    {
        return isset($this->messages[$lang][$field]);
    }

    public function get($field, $lang)
    {
        return $this->has($field, $lang) ? $this->messages[$lang][$field] : null;
    }
}
