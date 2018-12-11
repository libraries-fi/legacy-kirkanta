<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="regions")
 */
class Region extends TranslatableEntity implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Idx\Enabled
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="region")
     */
    protected $cities;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    public function __construct()
    {
        $this->cities = new DoctrineCollection;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCities()
    {
        return $this->cities;
    }
}
