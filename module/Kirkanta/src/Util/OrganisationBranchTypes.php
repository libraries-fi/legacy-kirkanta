<?php

namespace Kirkanta\Util;

use Zend\I18n\Translator\TranslatorInterface;

class OrganisationBranchTypes
{
    protected $types;

    public function __construct(TranslatorInterface $translator)
    {
        $tr = [$translator, 'translate'];
        $this->types = [
            'library' => $tr('Library'),
            'main_library' => $tr('Main library'),
            'regional' => $tr('Regional library'),
            'mobile' => $tr('Mobile library'),
            'home_service' => $tr('Home service'),
            'institutional' => $tr('Institutional library'),
            'children' => $tr('Children\'s library'),
            'music' => $tr('Music library'),
            'special' => $tr('Special library'),
            'vocational_college' => $tr('Vocational college library'),
            'school' => $tr('School library'),
            'polytechnic' => $tr('Polytechnic library'),
            'university' => $tr('University library'),
            'other' => $tr('Other library organisation'),
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
