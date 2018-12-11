<?php

namespace SamuForm\Form\Element;

use Zend\Form\Element;

class Grid extends Element
{
    public function addAllowed()
    {
        return $this->getOption('allow_add', false);
    }

    public function removeAllowed()
    {
        return $this->getOption('allow_remove', false);
    }

    public function shouldCreateTemplate()
    {
        return $this->getOption('should_create_template', true);
    }

    public function getCount()
    {
        return $this->option('count', 0);
    }

    public function setCount($count)
    {
        $this->assertRowCount($count);
        $this->setOption('count', $count);
    }
}
