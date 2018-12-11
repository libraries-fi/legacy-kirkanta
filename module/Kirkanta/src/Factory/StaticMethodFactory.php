<?php

namespace Kirkanta\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\MutableCreationOptionsTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Provides Symfony-like method for creating services using static
 * method MyClass::create(ContainerInterface $container)
 */
class StaticMethodFactory implements AbstractFactoryInterface, MutableCreationOptionsInterface
{
    use MutableCreationOptionsTrait;

    public function canCreateServiceWithName(ServiceLocatorInterface $manager, $name, $class)
    {
        return class_exists($class) && is_callable($class . '::create');
    }

    public function createServiceWithName(ServiceLocatorInterface $manager, $name, $class)
    {
        // Provides compatibility with ZF2 plugin managers.
        if ($manager instanceof AbstractPluginManager) {
            $manager = $manager->getServiceLocator();
        }
        return $class::create($manager, $this->getCreationOptions());
    }
}
