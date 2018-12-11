<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use Kirkanta\Util\ServiceTypes;

class ServiceReferenceListBuilder extends ServiceListBuilder
{
    protected $types;
    protected $column_map = [
        'name' => 'ref.name',
        'type' => 'ref.type',
    ];

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'type' => $this->tr('Type'),
                'short_description' => $this->tr('Description'),
            ])
            ->setWidth('name', 200)
            ->setWidth('type', 140)
            ->setSortable('name', true)
            ->transform('name', [$this, 'editLink'])
            ->transform('type', [$this, 'mapType']);
    }

    public function mapType($type)
    {
        if (!$this->types) {
            $this->types = new ServiceTypes($this->getTranslator());
        }
        return $this->types->map($type);
    }

    public function setQuery(QueryBuilder $builder, array $options = [])
    {
        $builder->join('e.service', 'ref');
        parent::setQuery($builder, $options);
    }

    protected function constructFilter(QueryBuilder $builder, $field, $value)
    {
        if ($field == 'name') {
            $builder->andWhere('LOWER(ref.name) LIKE LOWER(:name)');
            $builder->setParameter('name', '%' . $value . '%');
        } else {
            parent::constructFilter($builder, $field, $value);
        }
        return $builder;
    }
}
