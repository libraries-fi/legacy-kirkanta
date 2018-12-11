<?php

namespace Kirkanta\Finna\Doctrine\Subscriber;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Interop\Container\ContainerInterface;
use Kirkanta\Finna\Entity\ConsortiumData;
use KirkantaIndexing\Indexer;

/**
 * Update entity as its subresources are changed.
 */
class ReactToSubresources implements EventSubscriber
{
    private $container;

    public static function create(ContainerInterface $container)
    {
        return new static($container);
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($key)
    {
        if ($key == 'indexer') {
            return $this->container->get(Indexer::class);
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::preRemove, Events::prePersist, Events::preUpdate];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ConsortiumData) {
            $this->indexer->index($entity->getConsortium());
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ConsortiumData) {
            $this->indexer->index($entity->getConsortium());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ConsortiumData) {
            $this->indexer->index($entity->getConsortium());
        }
    }
}
