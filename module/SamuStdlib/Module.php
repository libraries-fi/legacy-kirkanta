<?php

namespace SamuStdlib;

/**
 * Module class for ZF2 to allow integration into ZF2 as a separate module.
 */
class Module {
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Samu\Stdlib' => __DIR__ . '/src/Samu/Stdlib',
                ),
            ),
        );
    }
}
