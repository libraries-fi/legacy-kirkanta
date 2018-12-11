<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class ProvincialLibraryListBuilder extends AbstractListBuilder
{
    public function getTitle()
    {
        return $this->tr('Provincial libraries');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'province' => $this->tr('Province'),
                // 'slug' => $this->tr('Slug'),
            ])
            ->setSortable('name', true)
            ->setSortable('province', true)
            // ->setSortable('slug', true)
            // ->setWidth('slug', 220)
            ->transform('name', [$this, 'editLink']);
    }
}
