<?php

namespace Kirkanta\Util;

use Zend\I18n\Translator\TranslatorInterface;

class RegionalLibraries
{
    protected $names;

    public function __construct(TranslatorInterface $translator)
    {
        $tr = [$translator, 'translate'];
        $this->names = [
            'ahvenanmaa' => $tr('Åland Islands Provincial Library area'),
            'etelasavo' => $tr('Southern Savo Provincial Library area'),
            'hame' => $tr('Häme Provincial Library area'),
            'kainu' => $tr('Kainuu Provincial Library area'),
            'keskisuomi' => $tr('Central Finland Provincial Library area'),
            'keskuskirjasto' => $tr('Central Library'),
            'kokkola' => $tr('Kokkola Provincial Library area'),
            'kouvola' => $tr('Kouvola Provincial Library area'),
            'lahti' => $tr('Lahti Provincial Library area'),
            'lappeenranta' => $tr('Lappeenranta Provincial Library area'),
            'lappi' => $tr('Lapland Provincial Library area'),
            'oulu' => $tr('Oulu Provincial Library area'),
            'pirkanmaa' => $tr('Pirkanmaa Provincial Library area'),
            'pohjoiskarjala' => $tr('North-Karelia Provincial Library area'),
            'pohjoissavo' => $tr('Northern Savo Provincial Library area'),
            'satakunta' => $tr('Satakunta Provincial Library area'),
            'seinajoki' => $tr('Seinäjoki Provincial Library area'),
            'uusimaa' => $tr('Uusimaa Provincial Library area'),
            'vaasa' => $tr('Vaasa Provincial Library area'),
            'varsinaissuomi' => $tr('Provincial Library of Varsinais-Suomi area'),
        ];
        asort($this->names);
    }

    public function getNames()
    {
        return $this->names;
    }

    public function map($name)
    {
        return isset($this->names[$name]) ? $this->names[$name] : null;
    }
}
