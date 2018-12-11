<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 */
class OrganisationWebLinkGroup extends WebLinkGroup
{

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="link_groups")
     */
    protected $organisation;

    /**
     * @ORM\OneToMany(targetEntity="OrganisationWebLink", mappedBy="link_group")
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $links;

    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;
        $organisation->addLinkGroups([$this]);
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }
}
