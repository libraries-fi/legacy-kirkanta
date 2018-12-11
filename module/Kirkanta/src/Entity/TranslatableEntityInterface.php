<?php

namespace Kirkanta\Entity;

use Kirkanta\I18n\TranslatableInterface;

interface TranslatableEntityInterface extends TranslatableInterface
{
    public function getTranslatedValues($field);
    public function getTranslatedValue($lang, $field);
    public function setTranslatedValue($lang, $field, $value);
}
