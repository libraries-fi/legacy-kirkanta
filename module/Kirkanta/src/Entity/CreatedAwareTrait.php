<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * Tracks entity created time.
 */
trait CreatedAwareTrait
{
    /**
     * @ORM\Column(type="datetime")
     * @Idx\Enabled
     * @Idx\DateTime
     * @Idx\Group(into="meta")
     */
    protected $created;

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated(DateTime $date)
    {
        $this->created = $date;
    }
}
