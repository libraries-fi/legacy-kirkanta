<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_types")
 * @Idx\Document(type="service")
 */
class ServiceType extends TranslatableEntity implements ModifiedAwareInterface, ServiceInterface, SluggableInterface, TemplateEntityInterface
{
    use ElasticMappableTrait;
    use ModifiedAwareTrait;
    use SluggableTrait;
    use TemplateEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Idx\Enabled
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     */
    protected $helmet_type_priority;

    /**
     * @ORM\OneToMany(targetEntity="Service", mappedBy="template")
     */
    protected $services;

    /**
     * @ORM\Column(type="integer")
     */
    protected $tr_score = 0;

    public function __construct()
    {
        $this->services = new ArrayCollection;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($n)
    {
        $this->name = $n;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getHelmetTypePriority()
    {
        return $this->helmet_type_priority;
    }

    public function setHelmetTypePriority($value)
    {
        $this->helmet_type_priority = $value;
    }

    public function getServices()
    {
        return $this->services;
    }
}
