<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 */
class AccessibilityReference extends TemplateReference
{
    protected $_source_field = 'accessibility';

    /**
     * @ORM\ManyToOne(targetEntity="AccessibilityFeature", inversedBy="references", fetch="EAGER")
     */
    protected $accessibility;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="accessibility")
     */
    protected $organisation;

    public function setAccessibility(AccessibilityFeature $accessibility)
    {
        $this->accessibility = $accessibility;
    }

    public function getAccessibility()
    {
        return $this->accessibility;
    }

    public function getName()
    {
        return $this->accessibility->getName();
    }

    public function getDescription()
    {
        return $this->accessibility->getDescription();
    }

    public function setOrganisation(Organisation $organisation = null)
    {
        if ($this->getOrganisation() != $organisation) {
            parent::setOrganisation($organisation);
            $organisation->addAccessibility([$this]);
        }
    }
}
