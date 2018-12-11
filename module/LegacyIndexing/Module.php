<?php

namespace LegacyIndexing;

use KirkantaIndexing\Event\IndexingEvent;
use KirkantaIndexing\Indexer;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();
        $config = $sm->get('Config')['indexing'];

        if ($config['enabled']) {
            $this->sm = $sm;

            $events = $sm->get('SharedEventManager');
            $events->attach(Indexer::class, IndexingEvent::INDEX, [$this, 'onDocumentIndex'], -1000);
            $events->attach(Indexer::class, IndexingEvent::REMOVE, [$this, 'onDocumentRemove'], -1000);

            $events = $sm->get('Doctrine\ORM\EntityManager')->getEventManager();
            $events->addEventSubscriber(new Subscriber\ConsortiumLegacyId);
            $events->addEventSubscriber(new Subscriber\LegacySchedules($sm));
            $events->addEventSubscriber(new Subscriber\MiscellaneousEntities($sm));
        }
    }

    public function __get($key)
    {
        if ($key == 'indexer') {
            $this->indexer = LegacyIndexer::create($this->sm);
            return $this->indexer;
        }
    }

    public function onDocumentIndex(IndexingEvent $event)
    {
        $this->indexer->index($event->object, $event->document);
        $this->indexer->flush();
    }

    public function onDocumentRemove(IndexingEvent $event)
    {
        $this->indexer->remove($event->object, $event->document);
        $this->indexer->flush();
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
