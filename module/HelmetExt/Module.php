<?php

namespace HelmetExt;

class Module
{
    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'Kirkanta\Helmet' => __DIR__ . '/src'
                ],
            ],
        ];
    }
}
