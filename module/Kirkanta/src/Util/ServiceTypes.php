<?php

namespace Kirkanta\Util;

use Zend\I18n\Translator\TranslatorInterface;

class ServiceTypes
{
    protected $types;

    public function __construct(TranslatorInterface $translator)
    {
        $tr = [$translator, 'translate'];
        $this->types = [
            'room' => $tr('Room'),
            'hardware' => $tr('Hardware'),
            'service' => $tr('Service'),
            'web_service' => $tr('Network'),
            'collection' => $tr('Collection'),
        ];
        asort($this->types);
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function map($type)
    {
        return isset($this->types[$type]) ? $this->types[$type] : null;
    }
}
