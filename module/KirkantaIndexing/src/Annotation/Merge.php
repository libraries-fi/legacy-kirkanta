<?php

namespace KirkantaIndexing\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Merge extends BaseAnnotation
{
    /*
     * List of fields that will be copied from the source. Can be left empty,
     * so that the whole source will be merged.
     */
    public $fields = [];
}
