<?php

namespace KirkantaIndexing\Annotation;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\AnnotationException;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Document implements Annotation
{
    public $type;

    public function __construct(array $defs)
    {
        foreach ($defs as $key => $value) {
            $this->{$key} = $value;
        }

        if (!$this->type) {
            throw AnnotationException::required('field', get_class(), 'Document type must be specified using "type" attribute.');
        }
    }
}
