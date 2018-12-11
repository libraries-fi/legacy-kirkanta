<?php

namespace Kirkanta\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Role;

class PictureEntityEventSubscriber implements EventSubscriber
{
    public function __construct()
    {

    }

    public function postPersist(LifecycleEventArgs $args)
    {

    }

    public function postUpdate(LifecycleEventArgs $args)
    {

    }

    protected function createImageSizes($image)
    {

    }
}
