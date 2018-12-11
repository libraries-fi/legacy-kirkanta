<?php

namespace Kirkanta\Event\Listener;

use Kirkanta\Controller\EntityController;
use Kirkanta\Controller\OrganisationController;
use Kirkanta\Entity\Notification;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Mvc\MvcEvent;

class InjectNotifications extends AbstractListenerAggregate
{
    protected $container = 'navigation';

    public function __construct()
    {
        $this->events = [
            [EntityController::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectNavigation']],
            [OrganisationController::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectNavigation']],
        ];
    }

    public function injectNavigation(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();
        $user = $sm->get('Zend\Authentication\AuthenticationService')->getIdentity();
        if ($user) {
            $repo = $sm->get('Doctrine\ORM\EntityManager')->getRepository(Notification::class);
            $notifications = $repo->findUnreadByUser($user);
            $event->getViewModel()->setVariable('notifications', $notifications);
        }
    }

}
