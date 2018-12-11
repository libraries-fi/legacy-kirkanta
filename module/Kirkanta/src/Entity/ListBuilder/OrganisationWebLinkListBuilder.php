<?php

namespace Kirkanta\Entity\ListBuilder;

class OrganisationWebLinkListBuilder extends AbstractListBuilder
{
    protected $default_sorting = [
        'weight' => 'asc',
        'id' => 'asc',
    ];
    
    protected $names;

    public function getTitle()
    {
        return $this->tr('Websites');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'name' => $this->tr('Name'),
                'url' => $this->tr('URL'),
            ])
            ->setSortable('name', false)
            ->setSortable('url', false)
//             ->setWidth('id', 50)
            ->transform('name', [$this, 'editLink']);
    }
}
