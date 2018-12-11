<?php

namespace Kirkanta\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Kirkanta\Event\Doctrine\QueryEventArgs;
use Kirkanta\Entity\Accessibility;
use Kirkanta\Entity\Service;

class ModifyTemplateEntityQuery implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [QueryEventArgs::preTemplateQuery];
    }

    public function preTemplateQuery(QueryEventArgs $args)
    {
        switch ($args->entity_class) {
            case Accessibility::class:
                $args->query->addOrderBy('t.name');
                break;

            case Service::class:
                $args->query->addOrderBy('t.type')->addOrderBy('t.name');
                break;
        }
    }
}
