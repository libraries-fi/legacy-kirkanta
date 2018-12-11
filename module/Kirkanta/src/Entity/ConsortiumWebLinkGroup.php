<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 */
class ConsortiumWebLinkGroup extends WebLinkGroup
{

    /**
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="link_groups")
     */
    protected $consortium;

    /**
     * @ORM\OneToMany(targetEntity="ConsortiumWebLink", mappedBy="link_group")
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $links;

    public function setConsortium(Consortium $consortium)
    {
        $this->consortium = $consortium;
        $consortium->addLinkGroups([$this]);
    }

    public function getConsortium()
    {
        return $this->consortium;
    }
}
