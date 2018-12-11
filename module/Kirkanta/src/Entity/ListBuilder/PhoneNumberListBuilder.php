<?php

namespace Kirkanta\Entity\ListBuilder;

class PhoneNumberListBuilder extends AbstractListBuilder
{
    protected $default_sorting = [
        'weight' => 'asc',
        'id' => 'asc',
    ];

    public function getTitle()
    {
        return $this->tr('Phone numbers');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'number' => $this->tr('Number'),
                'description' => $this->tr('Description'),
                'weight' => '',
            ])
            ->setSortable('name', false)
            ->setSortable('number', false)
            ->setSortable('description', false)
            ->transform('name', function($name, $i, $data) {
                return $this->editLink($name, $i, $data);
            });
    }
}
