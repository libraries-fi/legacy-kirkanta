<?php

namespace Kirkanta\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class DefaultEntityWeight implements EventSubscriber
{
    const DEFAULT_WEIGHT = 999;

    public function getSubscribedEvents()
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        if (method_exists($args->getObject(), 'setWeight')) {
            $args->getObject()->setWeight(self::DEFAULT_WEIGHT);
        }
    }
}
