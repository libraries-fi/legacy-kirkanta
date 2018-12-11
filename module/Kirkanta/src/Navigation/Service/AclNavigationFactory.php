<?php

namespace Kirkanta\Navigation\Service;

use Interop\Container\ContainerInterface;
use Kirkanta\Authentication\Guard\Route as RouteGuard;
use Zend\Navigation\Service\AbstractNavigationFactory;

class AclNavigationFactory extends AbstractNavigationFactory
{
    protected $name = 'default';

    public function getName()
    {
        return $this->name;
    }

    protected function preparePages(ContainerInterface $container, $pages)
    {
        $pages = parent::preparePages($container, $pages);

        if ($this->name == 'default') {
            foreach ($pages as &$page) {
                if (!empty($page['route'])) {
                    $page['resource'] = RouteGuard::ROUTE_PREFIX . $page['route'];
                }
            }
        }
        return $pages;
    }
}
