<?php

namespace LegacyIndexing\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Kirkanta\Entity\Consortium;

class ConsortiumLegacyId implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof Consortium) {
            $legacy_id = current(explode('-', $args->getEntity()->getSlug()));
            $args->getEntity()->setLegacyId($legacy_id);
        }
    }
}
