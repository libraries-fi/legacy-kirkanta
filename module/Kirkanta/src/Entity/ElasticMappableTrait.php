<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ElasticMappableTrait
{
    /**
     * @ORM\Column(type="string")
     */
    protected $elastic_id;

    public function getElasticId()
    {
        return $this->elastic_id;
    }

    public function setElasticId($id)
    {
        $this->elastic_id = $id;
    }
}
