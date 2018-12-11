<?php

namespace Kirkanta\Form\Element;

use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Entity\Role;

class RoleSelect extends EntitySelect
{
    public function init()
    {
        $this->setOptions($this->getOptions() + [
            'empty_option' => '',
            'target_class' => Role::class,
            'find_method' => [
                'name'   => 'findBy',
                'params' => [
                    'criteria' => [],
                    'orderBy'  => ['role_id' => 'asc']
                ]
            ]
        ]);
    }
}
