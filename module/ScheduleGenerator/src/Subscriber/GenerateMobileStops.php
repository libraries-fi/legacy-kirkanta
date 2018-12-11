<?php

namespace ScheduleGenerator\Subscriber;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Period;
use KirkantaIndexing\Indexer;
use ScheduleGenerator\DayGenerator;
use ScheduleGenerator\LibrarySchedules;
use Zend\ServiceManager\ServiceLocatorInterface;

class GenerateMobileStops implements EventSubscriber
{
    protected $sm;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
        $this->generator = LibrarySchedules::create($sm);
    }

    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postRemove, Events::postUpdate];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateRange($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateRange($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->updateRange($args);
    }

    protected function updateRange(LifecycleEventArgs $args)
    {
        if (!(($period = $args->getObject()) instanceof Period)) {
            return;
        }

        $period = $args->getObject();
        $library = $period->getOrganisation() ? $period->getOrganisation()->getParent() : null;

        if (!$library or $library->getBranchType() != 'mobile') {
            return;
        }

        $start = max($period->getValidFrom(), new DateTime);
        $end = min($period->getValidUntil(), new DateTime('+6 months')) ?: new DateTime('+6 months');

        $schedules = LibrarySchedules::generate($library, $start, $end);
    }

    protected function generator()
    {
        return new DayGenerator;
    }

    protected function indexer()
    {
        return $this->sm->get(Indexer::class);
    }
}
