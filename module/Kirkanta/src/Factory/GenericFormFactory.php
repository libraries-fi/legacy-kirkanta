<?php

namespace Kirkanta\Factory;

use Zend\Form\Form;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\MutableCreationOptionsTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class GenericFormFactory implements AbstractFactoryInterface, MutableCreationOptionsInterface
{
    use MutableCreationOptionsTrait;

    public function canCreateServiceWithName(ServiceLocatorInterface $form_manager, $name, $fqcn)
    {
        // var_dump($fqcn, is_a($fqcn, Form::class, true) && method_exists($fqcn, 'create'));
        return is_a($fqcn, Form::class, true) && method_exists($fqcn, 'create');
    }

    public function createServiceWithName(ServiceLocatorInterface $form_manager, $name, $fqcn)
    {
        $services = $form_manager->getServiceLocator();
        return call_user_func($fqcn . '::create', $services, $this->getCreationOptions());
    }
}
