<?php

namespace Kirkanta\Navigation\Service;

use Traversable;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Zend\Navigation\Service\AbstractNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class MobileStopFormNavigationFactory extends OrganisationFormNavigationFactory
{
    protected $name = 'mobile_stop';
}
