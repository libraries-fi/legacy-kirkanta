<?php

namespace Kirkanta\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="entity", type="string")
 * @ORM\DiscriminatorMap({"organisation" = "OrganisationWebLinkGroup", "consortium" = "ConsortiumWebLinkGroup"})
 * @ORM\Table(name="web_link_groups")
 */
abstract class WebLinkGroup extends TranslatableEntity implements GroupOwnershipAwareInterface
{
    use GroupOwnershipAwareTrait;

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
     */
    protected $identifier;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    public function __construct()
    {
        /*        var_dump($document['links']);

         * NOTE: $links is defined in subclasses as its type-specific!
         */
        $this->links = new ArrayCollection;
    }

    public function __toString()
    {
        try {
            return $this->getName();
        } catch (\Exception $e) {
            exit('caught');
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function setLinks($links)
    {
        $this->links->clear();
        $this->addLinks($items);
    }

    public function addLinks($items)
    {
        $this->addItems($this->links, $items, 'setLinkGroup');
    }

    public function removeLinks($items)
    {
        $this->removeItems($this->links, $items);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
