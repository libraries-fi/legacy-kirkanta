<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class EscapeHtml extends AbstractPlugin
{
    private $escape_html;

    public static function create(PluginManager $plugins)
    {
        $services = $plugins->getServiceLocator();
        $escaper = $services->get('ViewHelperManager')->get('EscapeHtml');
        return new static($escaper);
    }

    public function __construct($escape_html)
    {
        $this->escape_html = $escape_html;
    }

    public function __invoke($string = null)
    {
        if (!func_num_args()) {
            return $this;
        }

        return $this->escape($string);
    }

    public function escape($string)
    {
        $encoder = $this->escape_html;
        return $encoder($string);
    }
}
