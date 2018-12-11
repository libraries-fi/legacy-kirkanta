<?php

namespace Kirkanta\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use Kirkanta\Util\PeriodSections;

class PeriodListBuilder extends AbstractListBuilder
{
    protected $sections;

    public function getTitle()
    {
        return $this->tr('Schedules');
    }

    public function build($entities)
    {
        $table = $this->table()
            ->setData($entities)
            ->setColumns([
//                 'id' => $this->tr('ID'),
                'name' => $this->tr('Name'),
                'section' => $this->tr('Section'),
                'valid_from' => $this->tr('Validity'),

                // This column is included so that the optimized hydrator would
                // include it in extracted data. Can be removed later, when
                // Table builder is changed to pass the actual entity to
                // transform() instead of extracted data.
                'valid_until' => '',
            ])
            ->setSortable('name', true)
            ->setSortable('valid_from', true)
            ->setWidth('valid_until', 1)
            ->transform('name', function($name, $i, $data) {
                // Unset 'section' so that it doesn't overwrite the route param
                // with the same name.
                unset($data['section']);
                return $this->editLink($name, $i, $data);
            })
            ->transform('section', [$this, 'mapSection'])
            ->transform('valid_from', function($from, $i, $row) {
                if (!$from) {
                    return null;
                }
                if ($to = $row['valid_until']) {
                    return sprintf('%s - %s', $from->format('Y-m-d'), $to->format('Y-m-d'));
                } else {
                    return sprintf($this->tr('%s (continuous)'), $from->format('Y-m-d'));
                }
            })
            ->transform('valid_until', function($date) {
                return '';
            });

        if ($this->isAllowed('admin')) {
            $table->addColumn('group', $this->tr('Group'));
        }

        return $table;
    }

    protected function filterQuery(QueryBuilder $builder, array $filter, $prefix = 'e')
    {
        if (empty($filter['withexpired'])) {
            $query = sprintf('(%s.valid_until >= :expired OR %s.valid_until IS NULL)', $prefix, $prefix);
            $builder->andWhere($query);
            $builder->setParameter(':expired', date('Y-m-d', time() - 7 * 24 * 3600));
        }
        if (isset($filter['withexpired'])) {
            unset($filter['withexpired']);
        }
        return parent::filterQuery($builder, $filter, $prefix);
    }

    public function mapSection($type)
    {
        if (!$this->sections) {
            $this->sections = new PeriodSections($this->getTranslator());
        }
        return $this->sections->map($type);
    }
}
