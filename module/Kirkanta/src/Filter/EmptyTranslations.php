<?php

namespace Kirkanta\Filter;

use Kirkanta\I18n\Translations;
use Zend\Filter\AbstractFilter;

class EmptyTranslations extends AbstractFilter
{
    protected $options = [
        'locales' => [],
    ];

    public function filter($value)
    {
        if (is_array($value)) {
            $value = Translations::filterEmpty($value);
        }
        return $value;
    }

    public function hasContent($langdata)
    {
        foreach ($langdata as $value) {
            if (strlen(trim($value)) > 0) {
                return true;
            }
        }
        return false;
    }
}
