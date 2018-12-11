<?php

namespace SamuForm\Filter;

use Zend\Filter\AbstractFilter;

class EmptyCollection extends AbstractFilter
{
    public function filter($value)
    {
        if (!is_array($value)) {
            return [];
        }

        foreach ($value as $key => $val) {
            if ($val !== '' && !is_null($val)) {
                return $value;
            }
        }

        return [];
    }
}
