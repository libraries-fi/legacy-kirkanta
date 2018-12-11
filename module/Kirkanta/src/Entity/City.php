<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="cities")
 */
class City extends TranslatableEntity implements SluggableInterface
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
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="cities")
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $region;

    /**
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="cities")
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $consortium;

    /**
     * @ORM\ManyToOne(targetEntity="ProvincialLibrary")
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $provincial_library;

    /**
     * @ORM\OneToMany(targetEntity="Organisation",  mappedBy="city")
     */
    protected $organisations;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

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

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion(Region $region = null)
    {
        $this->region = $region;
    }

    public function getProvincialLibrary()
    {
        return $this->provincial_library;
    }

    public function setProvincialLibrary(ProvincialLibrary $library = null)
    {
        $this->provincial_library = $library;
    }

    public function getConsortium()
    {
        return $this->consortium;
    }

    public function setConsortium(Consortium $consortium = null)
    {
        $this->consortium = $consortium;
    }

    public function getOrganisations()
    {
        return $this->organisations;
    }
}
