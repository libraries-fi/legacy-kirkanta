<?php

namespace Kirkanta;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Router\RouteStackInterface;

class UrlBuilder
{
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('Router')
        );
    }

    public function __construct(RouteStackInterface $router)
    {
        $this->router = $router;
    }

    public function url($name, $params = [])
    {
        $options = [
            'name' => $name,
        ];
        return $this->router->assemble($params, $options);
    }
}
