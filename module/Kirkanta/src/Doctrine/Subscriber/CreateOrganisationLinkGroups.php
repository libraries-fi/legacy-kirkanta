<?php

namespace Kirkanta\Doctrine\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\OrganisationWebLinkGroup;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class CreateOrganisationLinkGroups implements EventSubscriber
{
    private $queue = [];

    private $data = [
        [
            'name' => 'Linkit',
            'identifier' => 'default',
            'translations' => [
                'en' => ['name' => 'Links'],
                'sv' => ['name' => 'Länkar'],
            ]
        ],
    ];

    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postFlush];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Organisation) {
            $this->queue[] = $entity;
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->queue) {
            $entities = $args->getEntityManager();
            $hydrator = new ClassMethodsHydrator;
            foreach ($this->queue as $organisation) {
                foreach ($this->data as $values) {
                    $group = $hydrator->hydrate($values, new OrganisationWebLinkGroup);
                    $organisation->addLinkGroups([$group]);
                }
            }
            $this->queue = [];
            $entities->flush();
        }
    }
}
