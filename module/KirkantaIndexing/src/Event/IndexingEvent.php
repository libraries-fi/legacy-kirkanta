<?php

namespace KirkantaIndexing\Event;

use Zend\EventManager\Event;

class IndexingEvent extends Event
{
    const INDEX = 'document.index';
    const REMOVE = 'document.remove';

    public $document;
    public $object;
    public $meta;
    public $response;

    public function __construct($object, array $document, array $meta)
    {
        $this->document = $document;
        $this->meta = $meta;
        $this->object = $object;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getMetaData()
    {
        return $this->meta;
    }
}
