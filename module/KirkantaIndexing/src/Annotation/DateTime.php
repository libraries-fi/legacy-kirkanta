<?php

namespace KirkantaIndexing\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class DateTime implements Annotation
{
    public $format = 'Y-m-d\TH:i:sP';

    public function __construct(array $defs)
    {
        foreach ($defs as $key => $value)
        {
            $this->{$key} = $value;
        }
    }
}
