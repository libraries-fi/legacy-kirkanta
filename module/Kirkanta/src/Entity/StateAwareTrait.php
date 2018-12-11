<?php

namespace Kirkanta\Entity;

trait StateAwareTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    protected $state = self::STATE_UNPUBLISHED;

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = (int)$state;
    }

    public function isPublished()
    {
        return $this->getState() > 0;
    }

    // public function delete()
    // {
    //      $this->state = self::STATE_DELETED;
    // }
    //
    // public function isDeleted()
    // {
    //     return $this->state ==  self::STATE_DELETED;
    // }
}
