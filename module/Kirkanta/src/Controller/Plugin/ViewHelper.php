<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class ViewHelper extends AbstractPlugin
{
    private $helpers;

    public static function create(PluginManager $plugins)
    {
        $services = $plugins->getServiceLocator();
        return new static($services->get('ViewHelperManager'));
    }

    public function __construct($helpers)
    {
        $this->helpers = $helpers;
    }

    public function __invoke($name)
    {
        if (!func_num_args()) {
            return $this;
        }

        return $this->get($name);
    }

    public function get($name)
    {
        return $this->helpers->get($name);
    }
}
