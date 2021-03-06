<?php

namespace Kirkanta\Factory;

use Kirkanta\Form\EntityForm;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\MutableCreationOptionsTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityFormFactory implements AbstractFactoryInterface, MutableCreationOptionsInterface
{
    use MutableCreationOptionsTrait;

    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $fqcn)
    {
        // var_dump(get_class($services));
        return is_a($fqcn, EntityForm::class, true);
    }

    public function createServiceWithName(ServiceLocatorInterface $services, $name, $fqcn)
    {
        $plugins = $services->getServiceLocator()->get('Kirkanta\EntityPluginManager');
        return call_user_func($fqcn . '::createInstance', $plugins, $this->getCreationOptions());
    }
}
