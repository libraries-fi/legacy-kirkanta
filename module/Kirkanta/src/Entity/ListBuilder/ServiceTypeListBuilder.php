<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

use Kirkanta\Util\ServiceTypes;

class ServiceTypeListBuilder extends AbstractListBuilder
{
    protected $types;

    public function getTitle()
    {
        return $this->tr('Service Types');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'name' => $this->tr('Name'),
                'type' => $this->tr('Type'),
                'description' => $this->tr('Description'),
                'services' => $this->tr('Usage'),
            ])
//             ->setWidth('id', 50)
            ->setWidth('name', 200)
            ->setWidth('type', 140)
            ->setWidth('services', 140)
            ->setSortable('name', true)
            ->transform('name', [$this, 'editLink'])
            ->transform('type', [$this, 'mapType'])
            ->transform('services', function($services, $key, $row) {
                $label = sprintf($this->tr('%d reference(s)'), count($services));
                return sprintf('<a href="/servicetype/%d/usage">%s</a>', $row['id'], $label);
            });
    }

    public function mapType($type)
    {
        if (!$this->types) {
            $this->types = new ServiceTypes($this->getTranslator());
        }
        return $this->types->map($type);
    }

    protected function constructFilter(QueryBuilder $builder, $field, $value)
    {
        if ($field == 'type') {
            $builder->andWhere('e.type = :type');
            $builder->setParameter('type', $value);
        } else {
            return parent::constructFilter($builder, $field, $value);
        }
    }
}
