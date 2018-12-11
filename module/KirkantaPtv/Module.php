<?php

namespace KirkantaPtv;

use Kirkanta\Controller\EntityController;
use Kirkanta\Entity\Organisation;
use Kirkanta\Ptv\EventListener\RedirectToSyncPage;
use Zend\ModuleManager\Feature\RouteProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();
        $events = $services->get('SharedEventManager');
        $events->attach(EntityController::class, MvcEvent::EVENT_DISPATCH, [new RedirectToSyncPage, 'onDispatch'], -1);

        $events->attach(EntityController::class, MvcEvent::EVENT_DISPATCH, function(MvcEvent $event) {
            $services = $event->getApplication()->getServiceManager();
            $ptv = $services->get(PtvManager::class);
            $acl = $services->get('BjyAuthorize\Service\Authorize');
            $route_match = $event->getRouteMatch();

            if ($acl->isAllowed('ptv') && $route_match->getMatchedRouteName() == 'organisation/edit') {
                $entity = $event->getResult()->getVariable('entity');
                $meta = $ptv->getEntityMeta($entity);

                $data = new ViewModel(['meta' => $meta, 'type' => 'organisation', 'id' => $entity->getId()]);
                $data->setTemplate('kirkanta/ptv/status-notification');
                $event->getResult()->addChild($data, 'ptv');
            }
        });
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'Kirkanta\Ptv' => __DIR__ . '/src'
                ],
            ],
        ];
    }
}
