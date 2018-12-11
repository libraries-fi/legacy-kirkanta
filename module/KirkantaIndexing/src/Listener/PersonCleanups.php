<?php

namespace KirkantaIndexing\Listener;

use Interop\Container\ContainerInterface;
use Kirkanta\Entity\Person;
use Kirkanta\PictureManager;
use KirkantaIndexing\Event\IndexingEvent;
use KirkantaIndexing\Indexer;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;

class PersonCleanups implements SharedListenerAggregateInterface
{
    protected $events;

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('Kirkanta\PictureManager'),
            $container->get('Config')
        );
    }

    public function __construct(PictureManager $pictures, array $config)
    {
        $this->pictures = $pictures;
        $this->config = $config;
        $this->events = [
            [Indexer::class, IndexingEvent::INDEX, [$this, 'onIndex'], 1000],
        ];
    }

    public function attachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->events as $params) {
            call_user_func_array([$events, 'attach'], $params);
        }
    }

    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->events as $params) {
            call_user_func_array([$events, 'detach'], $params);
        }
    }

    public function onIndex(IndexingEvent $event)
    {
        if (!$event->getObject() instanceof Person) {
            return;
        }

        $document = &$event->document;
        if (!$document['email_public']) {
            $document['email'] = null;
        }
        if ($document['picture']) {
            $document['picture'] = $this->pictures->urlForImage($document['picture'], 'thumb', true);
        }
        unset($document['email_public']);
    }
}
