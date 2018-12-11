<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;

trait GroupOwnershipAwareTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Role")
     */
    protected $group;

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Role $group = null)
    {
        $this->group = $group;
    }
}
