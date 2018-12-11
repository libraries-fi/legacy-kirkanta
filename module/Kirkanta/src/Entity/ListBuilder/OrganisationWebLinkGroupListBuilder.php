<?php

namespace Kirkanta\Entity\ListBuilder;

class OrganisationWebLinkGroupListBuilder extends AbstractListBuilder
{
    protected $names;

    public function getTitle()
    {
        return $this->tr('Link Groups');
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
            ->transform('name', [$this, 'editLink'])
            ;
    }
}
