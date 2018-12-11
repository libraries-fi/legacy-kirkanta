<?php

namespace Kirkanta\Form;

use ReflectionClass;

class EntityDeleteForm extends EntityForm
{
    protected $message;

    public function init()
    {
        $this->actions = self::SUBMIT_BUTTON | self::CANCEL_BUTTON;
        $this->message = $this->tr('Do you really want to delete this item?');
    }

    public function getInputFilterSpecification()
    {
        return [];
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getTitle()
    {
        $entity_class = (new ReflectionClass($this->getObject()))->getShortName();
        return sprintf('%s %s', $this->tr('Delete'), $this->tr($entity_class));
    }
}
