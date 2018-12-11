<?php

namespace KirkantaIndexing\Annotation;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\AnnotationException;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Reference extends BaseAnnotation
{
    /**
     * @Enum({"field", "values", "raw"})
     *
     * field: extract a single value defined by property 'field'
     * values: run the hydrator on the child object (WARNING: can cause an infinite loop!)
     */
    public $extract = 'values';

    /*
     * Defines a single field to be extracted.
     */
    public $field;

    /*
     * Used in conjunction with $field to extract translated versions aswell.
     */
    public $translated = false;

    /*
     * Can be used for enforce type of the reference.
     *
     * Options:
     *  - list := reference is a list of entities
     *  - entity := reference is a single entity
     */
    public $type;

    /*
     * Used with extract == "field" to copy value into a different field.
     */
    public $name;

    protected function validate()
    {
        if ($this->extract == 'field' and !$this->field) {
            throw AnnotationException::requiredError('field', get_class(), 'property', 'When extracting a field, the name of the field must be defined');
        }
    }

    public function isList()
    {
        if (is_null($this->type)) {
            return null;
        }
        return $this->type == 'list';
    }
}
