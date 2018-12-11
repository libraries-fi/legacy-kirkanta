<?php

namespace Kirkanta\Entity\ListBuilder;

class ConsortiumListBuilder extends AbstractListBuilder
{
    public function getTitle()
    {
        return $this->tr('Consortiums');
    }

    public function build($entities)
    {
        $table = $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'homepage' => $this->tr('Homepage'),
                'state' => $this->tr('Published'),
            ])
            ->setWidth('name', 250)
            ->setSortable('name', true)
            ->setWidth('state', 30)
            ->transform('name', [$this, 'editLink'])
            ->transform('homepage', function($url) {
                return sprintf('<a href="%s">%s</a>', $url, $url);
            })
            ->transform('state', [$this, 'mapState']);

        if ($this->isAllowed('admin')) {
            $table->addColumn('group', $this->tr('Group'));
        }

        return $table;
    }

    public function mapState($state)
    {
        return $state ? $this->tr('Yes') : $this->tr('No');
    }
}
