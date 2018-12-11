<?php

namespace ScheduleGenerator\Subscriber;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\Period;
use KirkantaIndexing\Indexer;
use ScheduleGenerator\DayGenerator;
use ScheduleGenerator\LibrarySchedules;
use Zend\ServiceManager\ServiceLocatorInterface;

class GenerateSchedules implements EventSubscriber
{
    private $sm;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
    }

    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::preRemove, Events::postUpdate];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->onUpdate($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->onUpdate($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->onUpdate($args);
    }

    private function onUpdate(LifecycleEventArgs $args)
    {
        if ($organisation = $this->findOrganisation($args)) {
            $this->updatePeriod($args->getObject(), $organisation);
        }
    }

    private function updatePeriod(Period $period, Organisation $organisation)
    {
        $start = new DateTime('Monday this week');
        $end = new DateTime('+12 months');
        $end = min($period->getValidUntil(), $end) ?: $end;
        $days = $this->generator()->generate($organisation, $start, $end);

        foreach ($days as $day) {
            unset($day['meta']);

            foreach ($day['sections'] as &$section) {
                unset($section['meta']);
            }
            $id = sprintf('%d::%s', $organisation->getId(), $day['date']);
            $this->indexer()->indexDocument('opening_time', $id, $day, 'libdir_schedules');
        }

        $this->removeJunkDays($organisation);
        $this->indexer()->flush();
    }

    private function removeJunkDays(Organisation $organisation)
    {
        if (count($organisation->getPeriods())) {
            $last_date = $this->findMaxDate($organisation->getPeriods());

            if (!$last_date) {
                return;
            }
        } else {
            $last_date = new DateTime('January 1');
        }

        $this->indexer()->getElasticClient()->deleteByQuery([
            'type' => 'opening_time',
            'index' => $this->indexer()->getIndexName('schedules'),
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['range' => [
                                'date' => [
                                    'gte' => $last_date->format('Y-m-d')
                                ]
                            ]],
                            ['term' => [
                                'organisation' => $organisation->getId(),
                            ]]
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function findOrganisation(LifecycleEventArgs $args)
    {
        /*
         * Load the Organisation entity from DB manually, because Period::getOrganisation() will
         * return NULL when the Period entity is being removed.
         */

        $em = $args->getEntityManager();
        $db = $em->getConnection();
        $period = $args->getObject();

        if ($period instanceof Period) {
            $period_id = $args->getObject()->getId();
            $organisation_id = $db
                ->executeQuery('SELECT organisation_id FROM periods WHERE id = ?', [$period_id])
                ->fetchColumn();

            if ($organisation_id) {
                $organisation = $em->find(Organisation::class, $organisation_id);
                return $organisation;
            }
        }
    }

    private function findMaxDate($periods)
    {
        $max = null;
        foreach ($periods as $period) {
            if (!$period->getValidUntil()) {
                return null;
            }
            if ($period->getValidUntil() > $max) {
                $max = $period->getValidUntil();
            }
        }
        return $max;
    }

    private function generator()
    {
        return LibrarySchedules::create($this->sm);
    }

    private function indexer()
    {
        return $this->sm->get(Indexer::class);
    }
}
