<?php

namespace KirkantaIndexing;

use Elasticsearch\Client;
use Zend\ServiceManager\ServiceLocatorInterface;

class Elasticsearch
{
    public static function create(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config')['elasticsearch'];
        $host = sprintf('%s:%d', $config['host'], $config['port']);
        return new Client(['hosts' => [$host]]);
    }
}
