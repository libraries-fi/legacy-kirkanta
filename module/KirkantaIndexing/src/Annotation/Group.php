<?php

namespace KirkantaIndexing\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Group extends BaseAnnotation
{
    /**
     * Defines the name for the target group/array
     */
    public $into;
}
