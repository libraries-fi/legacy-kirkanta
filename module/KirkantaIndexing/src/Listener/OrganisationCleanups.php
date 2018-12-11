<?php

namespace KirkantaIndexing\Listener;

use ArrayObject;
use Kirkanta\Entity\Organisation;
use Kirkanta\I18n\Translations;
use KirkantaIndexing\Event\IndexingEvent;
use KirkantaIndexing\Indexer;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;

use KirkantaIndexing\OrganisationCache;

class OrganisationCleanups implements SharedListenerAggregateInterface
{
    protected $events;
    private $cache;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->events = [
            [Indexer::class, IndexingEvent::INDEX, [$this, 'onIndex'], 1000],
        ];

        $this->cache = OrganisationCache::create();
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
        if (!$event->getObject() instanceof Organisation) {
            return;
        }

        $document = &$event->document;
        $document['address']['coordinates'] = array_take($event->document, 'coordinates');
        $document['extra']['building'] = array_take($event->document, 'building');
        $document['extra']['transit'] = array_take($event->document, 'transit');

        $this->formatCoordinates($document);
        $this->defaultPictureFirst($document['pictures']);
        $this->pictureSizes($document['pictures']);
        $this->serviceAndPersonPictureSizes($document['services']);
        $this->serviceAndPersonPictureSizes($document['persons']);
        $this->stripResourceMeta($document['persons']);
        $this->stripResourceMeta($document['pictures']);
        $this->stripResourceMeta($document['services']);
        $this->filterProtectedStaffEmails($document['persons']);
        $this->filterUnpublishedPersons($document['persons'], $event->getObject());
        $this->packAddressCity($document['address']);
        $this->consortiumPictureSizes($document);
        $this->fallbackConsortium($document, $event->object);
        $this->injectCustomData($document, $event->object);
        $this->mergeServiceTypeData($document);
        $this->removeUnpublishedConsortium($document, $event->getObject());

        if (!$event->getObject()->getAddress()) {
            $document['address'] = null;
        }

        $live_status = $this->cache->getItem($document['id'] . ':live-status');
        $time = $this->cache->getItem($document['id'] . ':current-time-rule');

        if (date('H:i') <= $time['closes']) {
            $document['status'] = $live_status;
        } else {
            $document['status'] = 0;
        }
    }

    private function removeUnpublishedConsortium(&$document, Organisation $organisation)
    {
        $consortium = $organisation->getConsortium();
        if ($consortium && !$consortium->isPublished()) {
            $document['consortium'] = null;
        }
    }

    protected function consortiumPictureSizes(&$organisation)
    {
        $sizes = ['small', 'medium'];

        if (!empty($organisation['city']['consortium']['logo'])) {
            $organisation['city']['consortium']['logo'] = $this->pictureFileSizes($organisation['city']['consortium'], 'logo', $sizes);
        }

        if (!empty($organisation['consortium']['logo'])) {
            $organisation['consortium']['logo'] = $this->pictureFileSizes($organisation['consortium'], 'logo', $sizes);
        }
    }

    protected function packAddressCity(&$address)
    {
        if (isset($address['city'])) {
            $address['city'] = $address['city']['name'];
        }
    }

    protected function formatCoordinates(array &$document)
    {
        if (!empty($document['address']['coordinates'])) {
            list($lat, $lon) = explode(',', $document['address']['coordinates']);
            $document['address']['coordinates'] = [
                'lat' => number_format(trim($lat), 8, '.', ''),
                'lon' => number_format(trim($lon), 8, '.', ''),
            ];
        }
    }

    protected function defaultPictureFirst(array &$pictures)
    {
        foreach ($pictures as $i => $picture) {
            if ($picture['default']) {
                array_splice($pictures, $i, 1);
                array_unshift($pictures, $picture);
                return;
            }
        }
    }

    protected function pictureSizes(array &$pictures, array $sizes = null)
    {
        $url_prefix = $this->config['indexing']['image_base_url'];
        $sizes = $sizes ?: array_keys($this->config['kirkanta']['pictures']['sizes']);
        foreach ($pictures as &$picture) {
            $picture['files'] = $this->pictureFileSizes($picture, 'filename');
            unset($picture['filename']);
        }
    }

    protected function serviceAndPersonPictureSizes(array &$items)
    {
        $url_prefix = $this->config['indexing']['image_base_url'];
        $sizes = array_keys($this->config['kirkanta']['pictures']['sizes']);
        foreach ($items as &$item) {
            $item['picture'] = $this->pictureFileSizes($item, 'picture');
        }
    }

    protected function pictureFileSizes(array $data, $key, array $sizes = null) {
        $value = $data[$key];

        if (!empty($value)) {
            $files = [];
            $url_prefix = $this->config['indexing']['image_base_url'];
            $sizes = $sizes ?: array_keys($this->config['kirkanta']['pictures']['sizes']);

            foreach ($sizes as $size) {
                $files[$size] = sprintf('%s/%s/%s', $url_prefix, $size, $data[$key]);
            }
            return $files;
        }
    }

    protected function stripResourceMeta(array &$resources)
    {
        foreach ($resources as &$item) {
            unset($item['group']);
            unset($item['shared']);
            unset($item['meta']);

            // Due to some bug Group annotation doesn't remove these already...
            unset($item['created']);
            unset($item['modified']);
        }
    }

    protected function filterProtectedStaffEmails(array &$persons)
    {
        foreach ($persons as &$person) {
            if (!$person['email_public']) {
                $person['email'] = null;
            }
            unset($person['email_public']);
        }
    }

    private function filterUnpublishedPersons(array &$persons, Organisation $organisation)
    {
        $cache = [];
        foreach ($organisation->getPersons() as $person) {
            if ($person->isPublished()) {
                $cache[] = $person->getId();
            }
        }
        foreach ($persons as $i => $person) {
            if (array_search($person['id'], $cache) === false) {
                unset($persons[$i]);
            }
        }
        $persons = array_values($persons);
    }

    private function fallbackConsortium(array &$document, Organisation $organisation)
    {
        if (empty($document['consortium']) && $organisation->isFallbackConsortiumAllowed()) {
            if (isset($document['city']['consortium'])) {
                $document['consortium'] = $document['city']['consortium'];
            }
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

    protected function mergeServiceTypeData(&$document)
    {
        foreach ($document['services'] as &$data) {
            $data['custom_name'] = $data['name'];
            $data['name'] = $data['template']['name'];
            $data['id'] = $data['template']['id'];
            $data['slug'] = $data['template']['slug'];
            $data['type'] = $data['template']['type'];

            unset($data['template']);
        }
    }
}
