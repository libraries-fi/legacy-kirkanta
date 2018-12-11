<?php

namespace Kirkanta\Entity\ListBuilder;

class NotificationListBuilder extends AbstractListBuilder
{
    protected $default_sorting = [
        'modified' => 'desc',
    ];
    
    public function getTitle()
    {
        return $this->tr('Notifications');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'title' => $this->tr('Title'),
                'modified' => $this->tr('Modified'),
            ])
            ->transform('title', [$this, 'editLink'])
            ->transform('modified', function($date) {
                return $date->format('Y-m-d H:i');
            });
    }
}
