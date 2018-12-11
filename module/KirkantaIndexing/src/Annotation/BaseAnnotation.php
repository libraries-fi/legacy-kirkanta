<?php

namespace KirkantaIndexing\Annotation;

use Doctrine\ORM\Mapping\Annotation;

abstract class BaseAnnotation implements Annotation
{
    public function __construct(array $defs)
    {
        foreach ($defs as $key => $value)
        {
            $this->{$key} = $value;
        }

        $this->validate();
    }

    protected function validate()
    {
        
    }
}
