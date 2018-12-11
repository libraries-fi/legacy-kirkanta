<?php

namespace Kirkanta\Entity;

trait TemplateEntityTrait
{
    protected $label_key = 'name';

    public function getLabel()
    {
        $getter = 'get' . ucfirst($this->label_key);
        return call_user_func([$this, $getter]);
    }
}
