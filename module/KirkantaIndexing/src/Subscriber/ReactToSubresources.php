<?php

namespace KirkantaIndexing\Subscriber;

use DateTime;
use ReflectionClass;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Interop\Container\ContainerInterface;
use Kirkanta\Entity\Consortium;
use Kirkanta\Entity\ConsortiumWebLink;
use Kirkanta\Entity\ConsortiumWebLinkGroup;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\OrganisationWebLink;
use Kirkanta\Entity\OrganisationWebLinkGroup;
use Kirkanta\Entity\Period;
use Kirkanta\Entity\Person;
use Kirkanta\Entity\PhoneNumber;
use Kirkanta\Entity\Picture;
use Kirkanta\Entity\Service;
use KirkantaIndexing\Indexer;

/**
 * Update document entity as its subresources are changed.
 */
class ReactToSubresources implements EventSubscriber
{
    private $container;
    private $entities;

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
        return [Events::onFlush, Events::postFlush];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions()
        );
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->entities) {
            return;
        }

        $has_organisation = [
            OrganisationWebLink::class,
            Person::class,
            PhoneNumber::class,
            Picture::class,
            Service::class,
        ];

        $has_consortium = [
            ConsortiumWebLink::class,
        ];

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $o_metadata = $em->getClassMetadata(Organisation::class);
        $c_metadata = $em->getClassMetadata(Consortium::class);

        foreach ($this->entities as $entity) {
            $match = array_filter($has_organisation, function($class) use ($entity) {
                return $entity instanceof $class;
            });

            if ($match && $entity->getOrganisation()) {
                $organisation = $entity->getOrganisation();
                $organisation->setModified(new DateTime);
                $uow->recomputeSingleEntityChangeSet($o_metadata, $organisation);
                break;
            }

            $match = array_filter($has_consortium, function($class) use ($entity) {
                return $entity instanceof $class;
            });

            if ($match && $entity->getConsortium()) {
                $consortium = $entity->getConsortium();
                $consortium->setModified(new DateTime);
                $uow->recomputeSingleEntityChangeSet($c_metadata, $consortium);
            }
        }


        if ($this->entities) {
            $this->entities = [];
            $em->flush();
        }
    }
}
