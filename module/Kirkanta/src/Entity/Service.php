<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="services_new")
 */
class Service extends TranslatableEntity implements GroupOwnershipAwareInterface, ModifiedAwareInterface, ServiceInterface, SharedEntityInterface
{
    use ElasticMappableTrait;
    use GroupOwnershipAwareTrait;
    use ModifiedAwareTrait;
    use SharedEntityTrait;

    protected $label_key = 'standardName';

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
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $short_description;

    /**
     * Price definition for the item. To allow more verbose descriptions,
     * the type of this field is set to string.
     *
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $price;

    /**
     * Basename of the picture file
     *
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $picture;

    /**
     * @ORM\Column(type="boolean")
     * @Idx\Enabled
     */
    protected $for_loan = false;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceType", inversedBy="services", fetch="EAGER")
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="services")
     */
    protected $organisation;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $phone_number;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $website;

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     */
    protected $helmet_priority;

    /**
     * @ORM\Column(type="boolean")
     * @Idx\Enabled
     */
    protected $shared = false;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        return $this->name = $name;
    }

    public function getStandardName()
    {
        if ($this->getTemplate()) {
            return $this->getTemplate()->getName();
        }
    }

    public function getType()
    {
        if ($this->getTemplate()) {
            return $this->getTemplate()->getType();
        }
    }

    public function getShortDescription()
    {
        return $this->short_description;
    }

    public function setShortDescription($description)
    {
        $this->short_description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getForLoan()
    {
        return $this->for_loan;
    }

    public function isForLoan()
    {
        return $this->getForLoan();
    }

    public function setForLoan($state)
    {
        $this->for_loan = (bool)$state;
    }

    public function setTemplate(ServiceType $service_type)
    {
        $this->template = $service_type;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getHelmetPriority()
    {
        return $this->helmet_priority;
    }

    public function setHelmetPriority($value)
    {
        $this->helmet_priority = $value;
    }

    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    public function setPhoneNumber($number)
    {
        $this->phone_number = $number;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($url)
    {
        $this->website = $url;
    }

    public function setOrganisation(Organisation $organisation)
    {
        if ($this->getOrganisation() != $organisation) {
            $this->organisation = $organisation;
            $organisation->addServices([$this]);
        }
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function getHelmetTypePriority()
    {
        if ($this->getTemplate()) {
            return $this->getTemplate()->getHelmetTypePriority();
        }
    }

    public function getLabel() {
        if ($name = $this->getName()) {
            return $name;
        }
        return $this->getStandardName();
    }
}
