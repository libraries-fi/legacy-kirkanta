<?php

namespace KirkantaIndexing\Listener;

use Kirkanta\Entity\Consortium;
use Kirkanta\I18n\Translations;
use KirkantaIndexing\Event\IndexingEvent;
use KirkantaIndexing\Indexer;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;

class ConsortiumCleanups implements SharedListenerAggregateInterface
{
    protected $events;

    public function __construct(array $config)
    {
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
        if (!$event->getObject() instanceof Consortium) {
            return;
        }

        $document = &$event->document;

        $this->injectCustomData($document, $event->object);

        if (!empty($document['logo'])) {
            $sizes = ['small', 'medium'];
            $document['logo'] = $this->pictureFileSizes($document, 'logo', $sizes);
        }
    }

    private function pictureFileSizes(array $data, $key, array $sizes = null) {
        $value = $data[$key];

        if (!empty($value)) {
            $files = [];
            // $url_prefix = $this->config['indexing']['image_base_url'];
            $url_prefix = 'https://kirkanta.kirjastot.fi/files/logos';
            $sizes = $sizes ?: array_keys($this->config['kirkanta']['pictures']['sizes']);

            foreach ($sizes as $size) {
                $files[$size] = sprintf('%s/%s/%s', $url_prefix, $size, $data[$key]);
            }
            return $files;
        }
    }

    protected function injectCustomData(array &$document, Organisation $organisation)
    {
        // Machine-readable data
        $document['extra']['data'] = [];

        // Human-readable data
        $document['extra']['info'] = [];

        foreach ($organisation->getCustomData() as $item) {
            if ($item instanceof ArrayObject) {
                $item = $item->getArrayCopy();
            }
            $item = Translations::mergeTranslations($item);
            if (!is_null($item['id'])) {
                $document['extra']['data'][] = $item;
            }

            if (!is_null($item['title']['fi']) && !is_null($item['value']['fi'])) {
                $document['extra']['info'][] = [
                    'title' => $item['title'],
                    'value' => $item['value'],
                ];
            }
        }
    }
}
