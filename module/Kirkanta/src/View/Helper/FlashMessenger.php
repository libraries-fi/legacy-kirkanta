<?php

namespace Kirkanta\View\Helper\FlashMessenger;

use Zend\View\Helper\FlashMessenger;

class FormMessages extends FlashMessenger
{
    protected $classMessages = array(
        PluginFlashMessenger::NAMESPACE_INFO => 'messages info',
        PluginFlashMessenger::NAMESPACE_ERROR => 'messages error',
        PluginFlashMessenger::NAMESPACE_SUCCESS => 'messages success',
        PluginFlashMessenger::NAMESPACE_DEFAULT => 'messages default',

        'form.success' => 'success',
        'form.error' => 'error',
    );
}
