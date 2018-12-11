<?php

namespace Kirkanta\Entity\ListBuilder;

class UserListBuilder extends AbstractListBuilder
{
    protected $default_sorting = [
        'username' => 'asc',
    ];

    public function getTitle()
    {
        return $this->tr('Users');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'id' => $this->tr('ID'),
                'username' => $this->tr('Username'),
                'email' => $this->tr('Email'),
                'role' => $this->tr('Group'),
                'last_login' => $this->tr('Last login'),
            ])
            ->setWidth('id', 50)
            ->setWidth('last_login', 140)
            ->setSortable('id', true)
            ->setSortable('username', true)
            ->setSortable('email', true)
            ->setSortable('last_login', true)
            ->transform('username', [$this, 'editLink'])
            ->transform('last_login', function($time) {
                return $this->plugin('ViewHelper')->get('FormatDateTime')->format($time) ?: '-';
            });
    }
}
