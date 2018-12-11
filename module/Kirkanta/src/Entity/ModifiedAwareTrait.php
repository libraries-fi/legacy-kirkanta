<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * Tracks entity change time.
 */
trait ModifiedAwareTrait
{
    use CreatedAwareTrait;

    /**
     * @ORM\Column(type="datetime")
     * @Idx\Enabled
     * @Idx\DateTime
     * @Idx\Group(into="meta")
     */
    protected $modified;

    public function getModified()
    {
        return $this->modified;
    }

    public function setModified(DateTime $time)
    {
        $this->modified = $time;
    }
}
