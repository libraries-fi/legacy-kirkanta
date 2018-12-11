<?php

namespace KirkantaIndexing\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Translated extends BaseAnnotation
{
    public $fallback = false;
}
