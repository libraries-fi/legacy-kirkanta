<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class FormMessages extends FlashMessenger
{
    public function addSuccessMessage($message)
    {
        $namespace = $this->getNamespace();
        $this->setNamespace('form.success');
        $this->addMessage($message);
        $this->setNamespace($namespace);

        return $this;
    }

    public function addErrorMessage($message)
    {
        $namespace = $this->getNamespace();
        $this->setNamespace('form.error');
        $this->addMessage($message);
        $this->setNamespace($namespace);

        return $this;
    }
}
