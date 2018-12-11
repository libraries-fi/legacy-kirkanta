<?php

namespace Kirkanta\Hydrator;

use Zend\Stdlib\Hydrator\ArraySerializable;

class ArrayObjectHydrator extends ArraySerializable
{
    public function hydrate(array $data, $object)
    {
        // exit('hydrate');
        if (is_null($object)) {
            return null;
        }
        return parent::hydrate($data, $object);
    }
}
