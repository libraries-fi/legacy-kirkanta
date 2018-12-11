<?php

namespace Kirkanta\Filter;

use Kirkanta\I18n\Translations;
use Zend\Filter\AbstractFilter;

class ResetArrayIndices extends AbstractFilter
{
    public function filter($array) {
        return array_values($array);
    }
}
