<?php

namespace Kirkanta\Ptv;

use Kirkanta\Entity\City;
use Kirkanta\Ptv\Util\Language;
use Kirkanta\Ptv\Util\Municipalities;

class MunicipalityMapper
{
    public function __construct()
    {
        $this->name_cache = new Municipalities;
    }

    public function convert(City $city)
    {
        $doc = [
            'code' => $this->name_cache->nameToid($city->getName()),
            'name' => [],
        ];

        foreach ($city->getTranslatedValues('name') as $lang => $name) {
            if (!empty($name) && Language::isAllowed($lang)) {
                $doc['name'][] = [
                    'language' => $lang,
                    'value' => $name,
                ];
            }
        }

        return $doc;
    }
}
