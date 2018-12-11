<?php

namespace KirkantaExport;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'Kirkanta\Export' => __DIR__ . '/src'
                ],
            ],
        ];
    }
}
