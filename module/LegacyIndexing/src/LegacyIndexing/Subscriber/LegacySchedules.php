<?php

namespace LegacyIndexing\Subscriber;

use DateTime;
use DateInterval;
use stdClass;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\Period;
use KirkantaIndexing\Indexer;
use ScheduleGenerator\DayGenerator;
use Zend\ServiceManager\ServiceLocatorInterface;

class LegacySchedules implements EventSubscriber
{
    private $sm;
    private $em;

    private $organisations;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
        $this->em = $sm->get('Doctrine\ORM\EntityManager');
    }

    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->organisations = [];

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();


        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Period) {
                $this->processPeriod($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Period) {
                $this->processPeriod($entity);
            }
        }

        $metadata = $em->getClassMetadata(Organisation::class);

        foreach ($this->organisations as $organisation) {
            $uow->recomputeSingleEntityChangeSet($metadata, $organisation);
        }
    }

    public function indexSchedules(array $DAYS, Organisation $organisation, $doctype)
    {
        if (empty($DAYS)) {
            return;
        }

        $this->indexer()->beginBulk();
        $days = array_slice(array_values($DAYS), 0, count($DAYS) - count($DAYS) % 7);

        $period_map = [];
        foreach ($organisation->getPeriods() as $period) {
            $period_map[$period->getId()] = $period;
        }

        for ($i = 0; $i < count($days); $i++) {
            $day_nr = date('N', strtotime($days[$i]['date']));

            // Skip days before the first Monday in the set.
            if ($day_nr > 1 && !isset($week, $id)) {
                continue;
            }

            if (date('N', strtotime($days[$i]['date'])) == 1) {
                if (isset($week, $id)) {
                    // Uses $week and $id from last round
                    $this->indexer()->indexDocument($doctype, $id, $week, 'libdir_legacy_schedules');
                }
                $organisation_id = $organisation->getElasticId() ?: $organisation->getId();
                $id = sprintf('%s::%s', $organisation_id, $days[$i]['date']);
                $week = [
                    'week_start' => $days[$i]['date'],
                    'name_fi' => $organisation->getName(),
                    'organisation' => $organisation_id,
                    'days' => [],
                    'meta' => [
                        'modified' => (new DateTime)->format('Y-m-d\TH:i:s'),
                    ],
                ];

                if ($i == count($days) - 1) {
                    break;
                }
            }

            $day = $days[$i];
            $data = [
                'date' => $day['date'] . 'T00:00:00',
                'weekday' => strtolower((new DateTime($day['date']))->format('l')),
                'closed' => $day['closed'],
                'times' => [],
            ];

            if (isset($days[$i]['period']) && isset($period_map[$days[$i]['period']])) {
                $period = $period_map[$days[$i]['period']];
                $trdata = $period->getTranslations();

                $data += [
                    'period_description_fi' => $period->getDescription(),
                    'period_description_en' => $trdata['en']['description'] ?? null,
                    'period_description_sv' => $trdata['sv']['description'] ?? null,
                ];
                $data['source'] = $period->getName();
            }

            if (!$day['closed']) {
                $data['opens'] = sprintf('%sT%s:00', $day['date'], $day['opens']);
                $data['closes'] = sprintf('%sT%s:00', $day['date'], $day['closes']);
                $data['times'] = $day['times'];
            }
            $week['days'][] = $data;
        }

        if (!empty($week['days']) && !empty($id)) {
            $this->indexer()->indexDocument($doctype, $id, $week, 'libdir_legacy_schedules');
        }

        $this->indexer()->flush();
    }

    private function extractOrganisationDocumentTimes(array $days)
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', time() + 24 * 3600);
        $new = [];

        foreach ($days as $day) {
            if (in_array($day['date'], [$today, $tomorrow])) {
                $data = [
                    'date' => $day['date'],
                    'day' => $day['day'],
                    'closed' => $day['closed'],
                    'opens' => $day['closed'] ? null : $day['opens'],
                    'closes' => $day['closed'] ? null : $day['closes'],
                    'times' => new stdClass,
                ];

                if (!empty($day['sections']->selfservice['times'])) {
                    $data['times']->selfservice = $day['sections']->selfservice['times'];
                }

                if (!empty($day['sections']->selfservice['opens'])) {
                    $data['closed'] = false;
                    $data['opens'] = min($data['opens'] ?: 99, $day['sections']->selfservice['opens']);
                }

                if (!empty($day['sections']->selfservice['closes'])) {
                    $alt_closes = $day['sections']->selfservice['closes'];

                    /*
                     * Here we try to handle the use-case where library is open
                     * beyond midnight. Thresholds are pulled out of a hat.
                     */
                    if ($data['closes'] >= '09:00' && $alt_closes <= '04:00') {
                        $data['closes'] = $alt_closes;
                    } else {
                        $data['closes'] = max($data['closes'], $day['sections']->selfservice['closes']);
                    }
                }

                if (!empty($day['times'])) {
                    $data['times']->main = $day['times'];
                }

                $new[] = $data;
            }
        }

        return $new;
    }

    public function processOrganisation(Organisation $organisation, DateTime $start, DateTime $end)
    {
        if ($start->format('N') != 1) {
            $start->sub(new DateInterval(sprintf('P%dD', $start->format('N') - 1)));
        }

        if ($end->format('N') != 7) {
            $end->add(new DateInterval(sprintf('P%dD', 7 - $start->format('N'))));
        }

        $days = $this->generator()->generateRange($organisation->getPeriods()->toArray(), $start, $end);
        $this->indexSchedules($days, $organisation, 'week');
        $this->removeJunkDays($organisation);

        $selfdays = $this->extractSection($days, 'selfservice');
        $this->indexSchedules($selfdays, $organisation, 'week_alt');
        $this->removeJunkDays($organisation, 'week_alt', 'selfservice');

        if ($start <= (new DateTime) && $end >= (new DateTime)) {
            $slice = $this->extractOrganisationDocumentTimes($days);

            foreach ($slice as &$data) {
                if ($data['closed'] == false) {
                    $data['closed'] = ($data['opens'] > date('H:i') || $data['closes'] < date('H:i'));
                }
                break;
            }

            $organisation->setCachedLegacyTimes($slice);
            $this->organisations[] = $organisation;
        }
    }

    private function processPeriod(Period $period)
    {
        $organisation = $this->findOrganisation($period);

        if ($organisation) {
            $start = max($period->getValidFrom(), new DateTime('-2 weeks'));
            $end = min($period->getValidUntil(), new DateTime('+6 months')) ?: new DateTime('+6 months');
            $this->processOrganisation($organisation, $start, $end);
        }
    }

    private function updateRange(LifecycleEventArgs $args)
    {
        $period = $args->getObject();

        if ($period instanceof Period) {
            $this->processPeriod($period);
        }
    }

    private function extractSection(array $source, $section) {
        $days = [];
        foreach ($source as $date => $srcday) {
            if (empty($srcday['sections']->{$section})) {
                continue;
            }
            $day = $srcday['sections']->{$section} + $srcday;
            unset($day['sections']);
            $days[$date] = $day;
        }
        return $days;
    }

    private function removeJunkDays(Organisation $organisation, $doctype = 'week', $period_type = 'default')
    {
        if (count($organisation->getPeriods($period_type))) {
            $last_date = $this->findMaxDate($organisation->getPeriods($period_type));

            if (!$last_date) {
                // Getting here means that there exists a continuous period, so we will want to
                // remove nothing.
                return;
            }
        } else {
            $last_date = new DateTime('January 1');
        }

        $organisation_id = $organisation->getElasticId() ?: $organisation->getId();

        $ok = $this->indexer()->getElasticClient()->deleteByQuery([
            'index' => 'libdir_legacy_schedules',
            'type' => $doctype,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['range' => [
                                'week_start' => [
                                    'gte' => $last_date->format('Y-m-d')
                                ]
                            ]],
                            ['term' => [
                                'organisation' => $organisation_id,
                            ]],
                        ]
                    ]
                ]
            ]
        ]);
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

    private function findOrganisation(Period $period)
    {

        if ($organisation = $period->getOrganisation()) {
            /*
             * Cannot use the code below when inserting new Period entities, because they
             don't exist in the DB yet.
             */
            return $organisation;
        } else {
            /*
             * Load the Organisation entity from DB manually, because Period::getOrganisation() will
             * return NULL when the Period entity is being removed.
             */

            $em = $this->em;
            $db = $em->getConnection();

            if ($period instanceof Period) {
                $period_id = $period->getId();
                $organisation_id = $db
                    ->executeQuery('SELECT organisation_id FROM periods WHERE id = ?', [$period_id])
                    ->fetchColumn();

                if ($organisation_id) {
                    $organisation = $em->find(Organisation::class, $organisation_id);
                    return $organisation;
                }
            }
        }
    }

    private function generator()
    {
        return new DayGenerator;
    }

    private function indexer()
    {
        return $this->sm->get(Indexer::class);
    }
}
