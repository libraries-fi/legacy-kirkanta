<?php

namespace Kirkanta\Finna\Doctrine\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Consortium;
use Kirkanta\Entity\ConsortiumWebLinkGroup;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class CreateConsortiumLinkGroups implements EventSubscriber
{
    private $queue = [];

    private $data = [
        [
            'name' => 'Finna-aineistot',
            'identifier' => 'finna_materials',
            'translations' => [
                'en' => ['name' => 'Finna materials'],
                'sv' => ['name' => 'Finna medel'],
            ]
        ],
        [
            'name' => 'Aineistojen käyttö',
            'identifier' => 'finna_usage_info',
            'translations' => [
                'en' => ['name' => 'Usage of Finna materials'],
                'sv' => ['name' => 'Användning av Finna material'],
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
        if ($entity instanceof Consortium) {
            $this->queue[] = $entity;
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->queue) {
            $entities = $args->getEntityManager();
            $hydrator = new ClassMethodsHydrator;
            foreach ($this->queue as $consortium) {
                foreach ($this->data as $values) {
                    $group = $hydrator->hydrate($values, new ConsortiumWebLinkGroup);
                    $consortium->addLinkGroups([$group]);
                }
            }
            $this->queue = [];
            $entities->flush();
        }
    }
}
