<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class PersonListBuilder extends AbstractListBuilder
{
    protected $default_sorting = [
        'last_name' => 'asc',
        'first_name' => 'asc',
    ];

    public function getTitle()
    {
        return $this->tr('Staff');
    }

    public function build($entities)
    {
        $table = $this->table()
            ->setData($entities)
            ->setColumns([
                'last_name' => $this->tr('Name'),
                'job_title' => $this->tr('Title'),
                'email' => $this->tr('Email'),
                'phone' => $this->tr('Phone'),
                'organisation' => $this->tr('Organisation'),
                'first_name' => '',
//                 'last_name' => '',
            ])
            ->setSortable('last_name', true)
            ->setSortable('job_title', true)
            ->setSortable('email', true)
            ->setWidth('phone', 120)
            ->transform('organisation', function($organisation) {
                if ($organisation) {
                    return $organisation->getName();
                }
            })
            ->transform('first_name', function() {
                return '';
            })
            ->transform('last_name', function($null, $i, $data) {
                $name = sprintf('%s, %s', $data['last_name'], $data['first_name']);
                return $this->editLink($name, $i, $data);
            });

        if ($this->isAllowed('admin')) {
            $table->addColumn('group', $this->tr('Group'));
        }

        return $table;
    }

    public function getSorting()
    {
        $sorting = parent::getSorting();
        if (empty($sorting['last_name'])) {
            return $sorting;
        }
        $sorting['first_name'] = $sorting['last_name'];
        return $sorting;
    }

    public function constructFilter(QueryBuilder $builder, $field, $value)
    {
        if ($field == 'name') {
            $fields = ['first_name', 'last_name', 'email'];
            $parts = preg_split('/\s+/', strtolower($value));

            foreach ($parts as $i => $part) {
                if (strlen($part) > 1) {
                    $expr = [];
                    foreach ($fields as $field) {
                        $expr[] = $builder->expr()->like(sprintf('LOWER(e.%s)', $field), ':' . $field . $i);
                        $builder->setParameter($field . $i, '%' . $part . '%');
                    }
                    $builder->andWhere(call_user_func_array([$builder->expr(), 'orX'], $expr));
                }
            }
        } else {
            return parent::constructFilter($builder, $field, $value);
        }
    }
}
