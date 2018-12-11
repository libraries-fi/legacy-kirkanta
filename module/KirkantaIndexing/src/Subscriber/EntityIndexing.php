<?php

namespace KirkantaIndexing\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\StateAwareInterface;
use KirkantaIndexing\Indexer;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityIndexing implements EventSubscriber
{
    private $sm;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
    }

    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::preRemove, Events::postUpdate];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof StateAwareInterface && $entity->isPublished()) {
            $this->sm->get(Indexer::class)->index($args->getObject());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->sm->get(Indexer::class)->remove($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $indexer = $this->sm->get(Indexer::class);
        if ($entity instanceof StateAwareInterface && !$entity->isPublished()) {
            $indexer->remove($entity);
        } else {
            $indexer->index($entity);
        }
    }
}
