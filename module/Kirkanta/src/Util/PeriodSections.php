<?php

namespace Kirkanta\Util;

use Zend\I18n\Translator\TranslatorInterface;

class PeriodSections
{
    protected $sections;

    public function __construct(TranslatorInterface $translator)
    {
        $tr = [$translator, 'translate'];
        $this->sections = [
            'default' => $tr('Regular timetables'),
            'selfservice' => $tr('Self service'),
            'magazines' => $tr('Reading room'),
        ];
        asort($this->sections);
    }

    public function getTypes()
    {
        return $this->sections;
    }

    public function map($type)
    {
        return isset($this->sections[$type]) ? $this->sections[$type] : null;
    }
}
