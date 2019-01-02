<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="periods")
 */
class Period extends TranslatableEntity implements GroupOwnershipAwareInterface, ModifiedAwareInterface, SharedEntityInterface
{
    use ElasticMappableTrait;
    use GroupOwnershipAwareTrait;
    use ModifiedAwareTrait;
    use SharedEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $name;

    /**
     * @ORM\Column(type="date")
     * @Idx\Enabled
     * @Idx\DateTime
     */
    protected $valid_from;

    /**
     * @ORM\Column(type="date")
     * @Idx\Enabled
     * @Idx\DateTime
     */
    protected $valid_until;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $section = 'default';

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     */
    protected $continuous = false;

    /**
     * @ORM\Column(type="boolean")
     * @Idx\Enabled
     */
    protected $shared = false;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="periods")
     */
    protected $organisation;

    /**
     * @ORM\Column(type="json_array")
     * @Idx\Enabled
     */
    protected $days;

    /**
     * @ORM\Column(type="json_array")
     * @Idx\Enabled
     */
    protected $translations = [];

    public function getLabel()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getValidFrom()
    {
        return $this->valid_from ? clone $this->valid_from : null;
    }

    public function setValidFrom($date)
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        $this->valid_from = $date;
    }

    public function getValidUntil()
    {
        return $this->valid_until ? clone $this->valid_until : null;
    }

    public function setValidUntil($date)
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        $this->valid_until = $date;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($d)
    {
        $this->description = $d;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection($section)
    {
        $this->section = $section;
    }

    public function getContinuous()
    {
        return $this->continuous or !$this->getValidUntil();
    }

    public function isContinuous()
    {
        return $this->getContinuous();
    }

    public function setContinuous($state)
    {
        $this->continuous = (bool)$state;

        if ($this->continuous) {
            $this->setValidUntil(null);
        }
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        if ($this->organisation) {
            $this->organisation->addPeriods([$this]);
        }
    }

    public function getDays()
    {
        return $this->days;
    }

    public function setDays($items)
    {
        if (!empty($items) && gettype($items[0]) == 'object') {
            $items = array_map('iterator_to_array', $items);
        }
        // exit('set' . count($items));
        $this->days = $items ?: [];
    }

    public function isClosedCompletely()
    {
        foreach ($this->days ?: [] as $day) {
            if (!empty($day['times'])) {
                foreach ($day['times'] as $time) {
                    if (!empty($time['opens']) || !empty($time['closes'])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getWeight()
    {
        if ($dt = $this->getValidUntil()) {
            return $dt->diff($this->getValidFrom())->days + 1;
        } else {
            return 9999;
        }
    }
}
