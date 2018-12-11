<?php

namespace Kirkanta\Util;

use Zend\I18n\Translator\TranslatorInterface;

class OrganisationTypes
{
    protected $types;

    public function __construct(TranslatorInterface $translator)
    {
        $tr = [$translator, 'translate'];
        $this->types = [
            'library' => $tr('Library'),
            'mobile_stop' => $tr('Mobile library stop'),
            'department' => $tr('Department'),
            'centralized_service' => $tr('Concentrated service'),
            'facility' => $tr('Library facility'),
            'archive' => $tr('Archive'),
            'museum' => $tr('Museum'),
            'other' => $tr('Other organisation'),
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
