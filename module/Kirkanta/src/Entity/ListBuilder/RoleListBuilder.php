<?php

namespace Kirkanta\Entity\ListBuilder;

class RoleListBuilder extends AbstractListBuilder
{
    protected $default_sorting = [
        'role_id' => 'asc',
    ];

    public function getTitle()
    {
        return $this->tr('User Groups');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'id' => $this->tr('ID'),
                'role_id' => $this->tr('Name'),
                'parent' => $this->tr('Parent'),
                'description' => $this->tr('Description'),
            ])
            ->setWidth('id', 50)
            ->setWidth('role_id', 200)
            ->transform('role_id', [$this, 'editLink']);
    }
}
