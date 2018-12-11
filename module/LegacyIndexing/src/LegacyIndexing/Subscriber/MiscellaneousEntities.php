<?php

namespace LegacyIndexing\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Consortium;
use Kirkanta\Entity\ProvincialLibrary;
use KirkantaIndexing\Indexer;
use Zend\ServiceManager\ServiceLocatorInterface;

class MiscellaneousEntities implements EventSubscriber
{
    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
    }

    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preRemove, Events::preUpdate];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->index($args->getObject());
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->index($args->getObject());
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->remove($args->getObject());
    }

    private function index($entity)
    {
        if ($entity instanceof Consortium && $entity->getLegacyId()) {
            $document = [
                'name' => $entity->getName(),
                'url' => $entity->getHomepage(),
            ];
            $this->indexer()->indexDocument('consortium', $entity->getLegacyId(), $document, $this->indexName());
        }

        if ($entity instanceof ProvincialLibrary && $entity->getLegacyId()) {
            $document = [
                'name_fi' => $entity->getName(),
                'name_en' => $entity->getTranslations()['en']['name'],
                'name_sv' => $entity->getTranslations()['sv']['name'],
            ];
            $this->indexer()->indexDocument('region', $entity->getLegacyId(), $document, $this->indexName());
        }

    }

    private function remove($entity)
    {

    }

    private function indexer()
    {
        return $this->sm->get(Indexer::class);
    }

    private function indexName($key = 'main')
    {
        return $this->sm->get('Config')['elastic_legacy']['indices'][$key];
    }
}
