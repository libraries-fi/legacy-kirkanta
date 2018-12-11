<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OrganisationWebLink extends WebLink
{
    /**
     * @ORM\ManyToOne(targetEntity="OrganisationWebLinkGroup", inversedBy="links")
     */
    protected $link_group;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="links")
     */
    protected $organisation;

    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;
        $organisation->addLinks([$this]);
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setLinkGroup(OrganisationWebLinkGroup $group)
    {
        $this->link_group = $group;
        $group->addLinks([$this]);
    }
}
