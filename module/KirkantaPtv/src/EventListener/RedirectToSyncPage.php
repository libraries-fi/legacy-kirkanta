<?php

namespace Kirkanta\Ptv\EventListener;

use Kirkanta\Entity\Organisation;
use Kirkanta\Ptv\PtvManager;
use Zend\Mvc\MvcEvent;

class RedirectToSyncPage
{
    public function onDispatch(MvcEvent $event)
    {
        $route_name = $event->getRouteMatch()->getMatchedRouteName();

        header('X-Foo-Bar: OK');

        if ($event->getRequest()->isPost() && $route_name == 'organisation/edit') {
            $em = $event->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');
            $ptv = $event->getApplication()->getServiceManager()->get(PtvManager::class);
            $organisation_id = $event->getRouteMatch()->getParam('organisation_id');
            $organisation = $em->find(Organisation::class, $organisation_id);

            if ($ptv->isEntityManaged($organisation)) {
                $router = $event->getApplication()->getServiceManager()->get('Router');
                $url = $router->assemble([
                    'type' => 'organisation',
                    'id' => $organisation_id
                ], ['name' => 'kirkanta_ptv/synchronize']);

                $response = $event->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->setStatusCode(302);

                $event->setResult($response);
            }
        }
    }
}
