<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class AccessibilityReferenceListBuilder extends AbstractListBuilder
{
    protected $column_map = [
        'name' => 'ref.name'
    ];

    public function getTitle()
    {
        return $this->tr('Accessibility');
    }

    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'description' => $this->tr('Description'),
            ])
            ->transform('name', [$this, 'editLink'])
            ->alter('description', function($d) { return mb_substr($d, 0, 50); });
    }

    public function setQuery(QueryBuilder $builder, array $options = [])
    {
        $builder->join('e.accessibility', 'ref');
        parent::setQuery($builder, $options);
    }

    protected function constructDefaultQuery()
    {
        $builder = parent::constructDefaultQuery();
        $builder->join('e.accessibility', 'ref');

        exit('asdsa');


        return $buider;
    }
}
