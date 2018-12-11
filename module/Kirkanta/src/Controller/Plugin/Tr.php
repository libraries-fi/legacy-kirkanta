<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Tr extends AbstractPlugin
{
    private $translator;

    public static function create(PluginManager $plugins)
    {
        $services = $plugins->getServiceLocator();
        return new static($services->get('MvcTranslator'));
    }

    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function __invoke($string = null)
    {
        if (!func_num_args()) {
            return $this;
        }

        return $this->translate($string);
    }

    public function translate($string)
    {
        return $this->getTranslator()->translate($string);
    }

    public function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->getController()
                ->getServiceLocator()
                ->get('ViewHelperManager')
                ->get('Translate')
                ->getTranslator();
        }
        return $this->translator;
    }
}
