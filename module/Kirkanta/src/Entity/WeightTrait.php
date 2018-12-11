<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;

trait WeightTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    protected $weight = 0;

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
}
