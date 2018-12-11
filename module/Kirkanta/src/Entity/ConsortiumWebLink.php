<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 */
class ConsortiumWebLink extends WebLink
{
    /**
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="links")
     */
    protected $consortium;

    /**
     * @ORM\ManyToOne(targetEntity="ConsortiumWebLinkGroup", inversedBy="links")
     */
    protected $link_group;

    public function setConsortium(Consortium $consortium = null)
    {
        $this->consortium = $consortium;

        if ($consortium) {
            $consortium->addLinks([$this]);
        }
    }

    public function getConsortium()
    {
        return $this->consortium;
    }

    public function setLinkGroup(ConsortiumWebLinkGroup $group = null)
    {
        $this->link_group = $group;

        if ($group) {
            $group->addLinks([$this]);
        }
    }
}
