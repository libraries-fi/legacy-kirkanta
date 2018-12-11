<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="accessibility")
 * @Idx\Document(type="accessibility")
 */
class AccessibilityFeature extends TranslatableEntity implements ModifiedAwareInterface, SluggableInterface, TemplateEntityInterface
{
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
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    /**
     * @ORM\OneToMany(targetEntity="AccessibilityReference", mappedBy="accessibility")
     */
    protected $references;

    public function __construct()
    {
        $this->references = new DoctrineCollection;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getReferences()
    {
        return $this->references;
    }
}
