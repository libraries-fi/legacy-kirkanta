<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="addresses")
 */
class Address extends TranslatableEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $street;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $area;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $info;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $zipcode;

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     */
    protected $box_number;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $city;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    public function isNull()
    {
        if (!empty($this->street)) {
            return false;
        }

        if (!empty($this->zipcode)) {
            return false;
        }

        if (!empty($this->box_number)) {
            return false;
        }

        if ($this->city) {
            return false;
        }

        return true;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getArea()
    {
        return $this->area;
    }

    public function setArea($area)
    {
        $this->area = $area;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getZipcode()
    {
        return $this->zipcode;
    }

    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    public function getBoxNumber()
    {
        return $this->box_number;
    }

    public function setBoxNumber($number)
    {
        $this->box_number = $number;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city = null)
    {
        $this->city = $city;
    }

    public function getCreated()
    {
        return null;
    }

    public function getModified()
    {
        return null;
    }

    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;
    }
}
