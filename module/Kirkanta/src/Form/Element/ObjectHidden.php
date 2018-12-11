<?php

namespace Kirkanta\Form\Element;

use DoctrineModule\Form\Element\Proxy;
use Zend\Form\Element\Hidden;

class ObjectHidden extends Hidden
{
    private $proxy;

    public function getProxy()
    {
        if (!$this->proxy) {
            $this->proxy = new Proxy;
        }

        return $this->proxy;
    }

    public function setOptions($options)
    {
        $this->getProxy()->setOptions($options);
        return parent::setOptions($options);
    }

    public function setValue($value)
    {/*
        $foo = $this->getProxy()->getValue($value);
        var_dump($foo);
        var_dump(gettype($foo));
        print '<br/><br/>';*/
        return parent::setValue($this->getProxy()->getValue($value));
    }
}
