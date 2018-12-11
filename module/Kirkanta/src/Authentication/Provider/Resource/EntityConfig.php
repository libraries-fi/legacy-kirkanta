<?php

namespace Kirkanta\Authentication\Provider\Resource;

use BjyAuthorize\Provider\Resource\ProviderInterface;

class EntityConfig implements ProviderInterface
{
    private $config = [];

    public function __construct(array $config, $container)
    {
        $this->config = $container->get('Config')['entities'];
    }

    public function getResources()
    {
        $resources = [];
        foreach ($this->config as $entity_config) {
            $key = 'entity.' . $entity_config['alias'];
            $resources[$key] = [];
        }
        return ['entity' => $resources];
    }
}
