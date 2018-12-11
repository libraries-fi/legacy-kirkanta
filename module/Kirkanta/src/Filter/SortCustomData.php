<?php

namespace Kirkanta\Filter;

use Kirkanta\I18n\Translations;
use Zend\Filter\AbstractFilter;

class SortCustomData extends AbstractFilter
{
    public function filter($array) {
        usort($array, function($a, $b) {
            $va = $a['title'] ?: $a['id'];
            $vb = $b['title'] ?: $b['id'];
            return strcasecmp($va, $vb);
        });
        return $array;
    }
}
