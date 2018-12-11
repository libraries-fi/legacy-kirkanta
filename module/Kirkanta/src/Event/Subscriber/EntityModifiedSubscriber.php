<?php

namespace Kirkanta\Event\Subscriber;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\CreatedAwareInterface;
use Kirkanta\Entity\ModifiedAwareInterface;

class EntityModifiedSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            $changed = false;

            if ($entity instanceof CreatedAwareInterface && !$entity->getCreated()) {
                $entity->setCreated(new DateTime);
                $changed = true;
            }

            if ($entity instanceof ModifiedAwareInterface) {
                $entity->setModified(new DateTime);
                $changed = true;
            }

            if ($changed) {
                $metadata = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($metadata, $entity);
            }
        }
    }
}
