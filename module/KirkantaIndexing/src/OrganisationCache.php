<?php

namespace KirkantaIndexing;

use Zend\Cache\StorageFactory;

class OrganisationCache
{
    public static function create()
    {
        $cache = StorageFactory::factory([
            'adapter' => [
                'name' => 'apc',
                'options' => [
                    'ttl' => 3600,
                    'namespace' => 'organisation',
                ],
            ]
        ]);

        return $cache;
    }
}
