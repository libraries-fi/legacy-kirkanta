<?php

namespace Kirkanta\Entity\ListBuilder;

use Kirkanta\Util\RegionalLibraries;

class CityListBuilder extends AbstractListBuilder
{
    protected $names;

    public function getTitle()
    {
        return $this->tr('Cities');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'name' => $this->tr('Name'),
                'region' => $this->tr('Region'),
                'consortium' => $this->tr('Consortium'),
                'provincial_library' => $this->tr('Provincial Library'),
            ])
//             ->setWidth('id', 50)
            ->transform('name', [$this, 'editLink']);
    }

    public function mapLibraryName($name)
    {
        if (!$this->names) {
            $this->names = new RegionalLibraries($this->getTranslator());
        }
        return $this->names->map($name);
    }
}
