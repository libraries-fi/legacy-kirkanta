<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

use Kirkanta\Entity\ServiceType;
use Kirkanta\Util\ServiceTypes;

class ServiceListBuilder extends AbstractListBuilder
{
    protected $types;

    protected $default_sorting = [
        'standard_name' => 'asc',
    ];

    protected $column_map = [
        'standard_name' => 't.name',
    ];

    public function getTitle()
    {
        return $this->tr('Services');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'standard_name' => $this->tr('Standard name'),
                'name' => $this->tr('Name'),
                'type' => $this->tr('Type'),
                'short_description' => $this->tr('Description'),
            ])
//             ->setWidth('id', 50)
            ->setWidth('name', 200)
            ->setWidth('type', 140)
            ->setSortable('standard_name', true)
            ->setSortable('name', true)
            ->transform('standard_name', [$this, 'editLink'])
            ->transform('type', [$this, 'mapType']);
    }

    public function mapType($type)
    {
        if (!$this->types) {
            $this->types = new ServiceTypes($this->getTranslator());
        }
        return $this->types->map($type);
    }

    protected function sortQuery(QueryBuilder $builder, array $sorting, $prefix = 'e')
    {
        if (isset($sorting['standard_name'])) {
            $builder->join('e.template', 't');
        }
        return parent::sortQuery($builder, $sorting, $prefix);
    }

    protected function constructFilter(QueryBuilder $builder, $field, $value)
    {
        if ($field == 'type') {
            $builder->andWhere('t.type = :type');
            $builder->setParameter('type', $value);
        } elseif ($field == 'name') {
            $value = strtolower('%' . $value . '%');
            // $builder->join('e.template', 't');
            $builder->andWhere('(LOWER(e.name) LIKE :name OR LOWER(t.name) LIKE :standard_name)');

            // $builder->andWhere('LOWER(e.name) LIKE :name');

            $builder->setParameter('name', $value);
            $builder->setParameter('standard_name', $value);
        } else {
            return parent::constructFilter($builder, $field, $value);
        }
    }
}
