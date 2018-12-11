<?php

namespace Kirkanta;

/**
 * NOTE: Designed so that each instance is used only with one type of entity!
 */
class EntityUrlBuilder
{
    protected $config;
    protected $plugin;
    protected $routes = [];

    public function __construct($config, $url_plugin) {
        $this->config = $config;
        $this->plugin = $url_plugin;
    }

    public function setUrlPrototype($name, $route, array $params = [])
    {
        if (is_null($route)) {
            $route = $this->getRoute($name);
        }

        $this->routes[$name] = [
            'route' => $route,
            'params' => $params,
        ];
    }

    public function getUrlPrototype($name, array $params = [])
    {
        /*
         * Fixes the problem that older code uses generic 'id' parameter instead
         * of class-specific key.
         */
        if (isset($params['id'])) {
            $params[$this->config['alias'] . '_id'] = $params['id'];
        }

        if (isset($this->routes[$name])) {
            $route = $this->routes[$name];
        } else {
            $route = [
                'route' => $this->getRoute($name),
                'params' => [],
            ];
        }
        $route['params'] = $params + $route['params'];
        return $route;
    }

    public function getUrl($name, $params = [])
    {
        if (is_object($params)) {
            $params = ['id' => $params->getId()];
        }

        $proto = $this->getUrlPrototype($name, $params);

        return $this->plugin->url($proto['route'], $proto['params']);
    }

    public function getRoute($name)
    {
        $routes = $this->config['routes'];
        return isset($routes[$name]) ? $routes[$name] : null;
    }
}
