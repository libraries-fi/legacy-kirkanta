<?php

namespace Kirkanta\Entity\ListBuilder;

class RegionListBuilder extends AbstractListBuilder
{
    public function getTitle()
    {
        return $this->tr('Regions');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'name' => $this->tr('Name'),
            ])
//             ->setWidth('id', 50)
            ->transform('name', [$this, 'editLink']);
    }
}
