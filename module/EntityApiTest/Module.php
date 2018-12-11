<?php

namespace EntityApiTest;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return[
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'KirkantaTest\EntityApi' => __DIR__ . '/src/',
                ],
            ],
        ];
    }
}
