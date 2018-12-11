<?php

namespace Kirkanta\Navigation\Service;

use Traversable;
use Interop\Container\ContainerInterface;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Zend\Navigation\Service\AbstractNavigationFactory;

class OrganisationFormNavigationFactory extends AclNavigationFactory
{
    protected $name = 'organisation';
    protected $param = 'organisation_id';

    protected function preparePages(ContainerInterface $container, $pages)
    {
        $application = $container->get('Application');
        $match = $application->getMvcEvent()->getRouteMatch();
        $organisation_id = $match->getParam($this->param, $match->getParam('id'));
        $pages = $this->injectOrganisationId($organisation_id, $pages);
        $pages = $this->injectResourcePages($container, $organisation_id, $pages);
        $pages = parent::preparePages($container, $pages);
        return $pages;
    }

    protected function injectOrganisationId($id, $pages)
    {
        foreach ($pages as &$page) {
            $page['params'][$this->param] = $id;
            if (isset($page['pages'])) {
                $page['pages'] = $this->injectOrganisationId($id, $page['pages']);
            }
        }
        return $pages;
    }

    protected function injectResourcePages(ContainerInterface $container, $organisation_id, $pages)
    {
        $em = $container->get('Doctrine\ORM\EntityManager');
        $organisation = $em->find('Kirkanta\Entity\Organisation', $organisation_id);

        if (!$organisation) {
            return $pages;
        }

        $hydrator = new DoctrineHydrator($em);
        $data = $hydrator->extract($organisation);

        if ($organisation) {
            foreach ($pages as &$page) {
                if (!isset($page['params']['section'])) {
                    continue;
                }
                $section = $page['params']['section'];

                if (isset($data[$section]) && $data[$section] instanceof Traversable) {

                    /*
                     * Create a fake page so that parent items will be properly
                     * highlighted in the menu
                     */
                    $page['pages'] = [
                        [
                            'label' => 'Edit',
                            'route' => $page['route'] . '/edit',
                            'params' => $page['params'],
                        ],
                    ];

                    /*
                     * NOTE: This is actually very expensive, because each many-reference
                     * will be fetched using separate queries!
                     */
//                     $page['pages'] = $this->createResourcePages($data, $section);
                }
            }
        }

        return $pages;
    }

    protected function createResourcePages($data, $section)
    {
        $route = 'organisation/resources/edit';
        $params = [
            $this->param => $data['id'],
            'section' => $section,
        ];
        $pages = [];
        foreach ($data[$section] as $resource) {
            $pages[] = [
                'label' => $resource->getName(),
                'route' => $route,
                'params' => $params + ['id' => $resource->getId()]
            ];
        }
        return $pages;
    }
}
