<?php

namespace Kirkanta\Entity\ListBuilder;

use Kirkanta\Util\OrganisationBranchTypes;
use Kirkanta\Util\OrganisationTypes;

class OrganisationListBuilder extends AbstractListBuilder
{
    protected $types;
    protected $branch_types;

    public function getTitle()
    {
        return $this->tr('Organisations');
    }

    public function build($entities)
    {
        $table = $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'type' => $this->tr('Type'),
                'branch_type' => $this->tr('Branch'),
                'state' => $this->tr('Published'),
            ])
            ->setWidth('state', 30)
            ->setSortable('name', true)
            ->transform('name', [$this, 'editLink'])
            ->transform('type', [$this, 'mapType'])
            ->transform('branch_type', [$this, 'mapBranchType'])
            ->transform('state', [$this, 'mapState']);

        if ($this->isAllowed('admin')) {
            $table->addColumn('group', $this->tr('Group'));
        }

        return $table;
    }

    public function mapType($type)
    {
        if (!$this->types) {
            $this->types = new OrganisationTypes($this->getTranslator());
        }
        return $this->types->map($type);
    }

    public function mapBranchType($type)
    {
        if (!$this->branch_types) {
            $this->branch_types = new OrganisationBranchTypes($this->getTranslator());
        }
        return $this->branch_types->map($type);
    }

    public function mapState($state)
    {
        return $state ? $this->tr('Yes') : $this->tr('No');
    }
}
