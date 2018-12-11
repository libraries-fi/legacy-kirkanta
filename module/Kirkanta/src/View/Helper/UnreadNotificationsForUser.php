<?php

namespace Kirkanta\View\Helper;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\AbstractHelper;
use Kirkanta\Entity\User;

class UnreadNotificationsForUser extends AbstractHelper implements ServiceManagerAwareInterface
{
    private $service_locator;

    public function __invoke(User $user = null)
    {
        if (!func_num_args()) {
            return $this;
        }

        return $this->find($user);
    }

    public function find(User $user = null)
    {
        $this->getServiceManager()
            ->get('Doctrine\ORM\EntityManager')
            ->getRepository('Kirkanta\Entity\Notification')
            ->findUnreadNotificationsByUser($user);
    }

    public function setServiceManager(ServiceManager $sl)
    {
        $this->service_locator = $sl;
    }

    public function getServiceManager()
    {
        return $this->service_locator;
    }
}
