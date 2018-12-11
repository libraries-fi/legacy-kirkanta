<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;
use Kirkanta\Finna\Entity\ConsortiumData as FinnaConsortiumData;

/**
 * @ORM\Entity
 * @ORM\Table(name="consortiums")
 * @Idx\Document(type="consortium")
 */
class Consortium extends TranslatableEntity implements GroupOwnershipAwareInterface, ModifiedAwareInterface, SluggableInterface, StateAwareInterface
{
    use GroupOwnershipAwareTrait;
    use ModifiedAwareTrait;
    use SluggableTrait;
    use StateAwareTrait;

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
     * @Idx\Translated(fallback=true)
     */
    protected $homepage;

    /**
     * @ORM\Column(type="string")
     */
    protected $legacy_id;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="consortium")
     */
    protected $cities;

    /**
     * @ORM\OneToMany(targetEntity="Organisation", mappedBy="consortium")
     */
    protected $organisations;

    /**
     * @ORM\OneToOne(targetEntity="Kirkanta\Finna\Entity\ConsortiumData", mappedBy="consortium", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @Idx\Enabled
     * @Idx\Reference(name="finna")
     */
    protected $finna_data;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $description;

    /**
     * Basename of the logo file
     *
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $logo;

    /**
     * Marks the consortium as 'special', which currently means a Finna-specific consortium.
     *
     * @ORM\Column(type="boolean")
     * @Idx\Enabled
     */
    protected $special = false;

    /**
     * @ORM\OneToMany(targetEntity="ConsortiumWebLink", mappedBy="consortium", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="ConsortiumWebLinkGroup", mappedBy="consortium", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $link_groups;

    public function __construct()
    {
        $this->state = self::STATE_PUBLISHED;
        $this->links = new ArrayCollection;
        $this->link_groups = new ArrayCollection;
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

    public function getHomepage()
    {
        return $this->homepage;
    }

    public function setHomepage($url)
    {
        $this->homepage = $url;
    }

    public function getCities()
    {
        return $this->cities;
    }

    public function setLegacyId($id)
    {
        $this->legacy_id = $id;
    }

    public function getLegacyId()
    {
        return $this->legacy_id;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setSpecial($state)
    {
        $this->special = (bool)$state;
    }

    public function isSpecial()
    {
        return $this->special;
    }

    public function getSpecial()
    {
        return $this->isSpecial();
    }

    public function setFinnaData(FinnaConsortiumData $data = null)
    {
        $this->finna_data = $data;
        if ($data) {
            $data->setConsortium($this);
        }
    }

    public function getFinnaData()
    {
        return $this->finna_data;
    }

    public function getOrganisations()
    {
        return $this->organisations;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function setLinks($items)
    {
        $this->links->clear();
        $this->addLinks($items);
    }

    public function addLinks($items)
    {
        $this->addItems($this->links, $items, 'setConsortium');
    }

    public function removeLinks($items)
    {
        $this->removeItems($this->links, $items, function($link) {
            $link->setConsortium(null);
            $link->getLinkGroup()->removeLinks([$link]);
        });
    }

    public function getLinkGroups()
    {
        return $this->link_groups;
    }

    public function setLinkGroups($items)
    {
        $this->link_groups->clear();
        $this->addLinkGroups($items);
    }

    public function addLinkGroups($items)
    {
        $this->addItems($this->link_groups, $items, 'setConsortium');
    }

    public function removeLinkGroups($items)
    {
        foreach ($items as $group) {
            $this->removeLinks($group->getLinks());
        };

        $this->removeItems($this->link_groups, $items, 'setConsortium');
    }
}
