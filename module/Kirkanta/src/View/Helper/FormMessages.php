<?php

namespace Kirkanta\View\Helper;

use Zend\Mvc\Controller\Plugin\FlashMessenger as PluginFlashMessenger;
use Zend\View\Helper\FlashMessenger;

class FormMessages extends FlashMessenger
{

    const NAMESPACE_FORM_SUCCESS = 'form.success';
    const NAMESPACE_FORM_ERROR = 'form.error';

    public function __construct()
    {
        $this->classMessages += [
            'form.success' => 'success',
            'form.error' => 'error',
        ];
    }

    public function hasMessages() {
        $plug = $this->getPluginFlashMessenger();
        $namespaces = [self::NAMESPACE_FORM_SUCCESS, self::NAMESPACE_FORM_ERROR];
        $has = false;
        $initial = $this->getNamespace();

        foreach ($namespaces as $ns) {
            $this->setNamespace($ns);
            $has |= parent::hasMessages();
        }

        return $has;
    }
}
