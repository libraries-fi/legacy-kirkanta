<?php

namespace ScheduleGenerator\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Kirkanta\Entity\Organisation;
use KirkantaIndexing\Indexer;
use ScheduleGenerator\DayGenerator;
use ScheduleGenerator\LibrarySchedules;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager;

use KirkantaIndexing\OrganisationCache;

class ScheduleController extends AbstractActionController
{
    use ProvidesObjectManager;

    private $indexer;
    private $schedules;

    public static function create(ControllerManager $cm)
    {
        $services = $cm->getServiceLocator();

        return new static(
            $services->get('Doctrine\ORM\EntityManager'),
            $services->get('KirkantaIndexing\Indexer'),
            LibrarySchedules::create($services)
        );
    }

    public static function liveStatus(array $day)
    {
        // Add fifteen minutes because schedule generator should be ran
        // fifteen minutes beforehand.
        $now = date('H:i', time() + 60 * 15);

        foreach ($day['times'] as $time) {
            if ($time['opens'] <= $now && $time['closes'] > $now) {
                return [1, $time];
            }
        }

        if (!empty($day['sections']->selfservice)) {
            foreach ($day['sections']->selfservice['times'] as $time) {
                if ($time['opens'] <= $now && $time['closes'] > $now) {
                    return [2, $time];
                }
            }
        }

        return [0, null];
    }

    public function __construct(EntityManagerInterface $entity_manager, Indexer $indexer, LibrarySchedules $schedules)
    {
        $this->setObjectManager($entity_manager);
        $this->indexer = $indexer;
        $this->schedules = $schedules;
    }

    public function schedulesAction()
    {
        $start = new DateTime('Monday this week');
        $end = new DateTime('+12 months');

        $blob = 300;
        $skip = $this->params()->fromQuery('skip', 0);

        $result = $this->getObjectManager()->createQueryBuilder()
            ->select('o')
            ->from(Organisation::class, 'o')
            ->addOrderBy('o.id')
            ->where('o.state = 1')

            // Pasila
            // ->andWhere('o.id = 84924')

            ->setFirstResult($skip)
            ->setMaxResults($blob)
            ->getQuery()
            ->getResult();

        if (!count($result)) {
            exit('Finished' . PHP_EOL);
        }

        $today = date('Y-m-d');
        $cache = OrganisationCache::create();

        foreach ($result as $organisation) {
            $days = $this->schedules->generate($organisation, $start, $end);
            $this->indexer->beginBulk();

            foreach ($days as $day) {
                if ($day['date'] == $today) {
                    list($status, $time) = self::liveStatus($day);
                    $oid = $organisation->getId();
                    $cache->setItem($oid . ':live-status', $status);
                    $cache->setItem($oid . ':current-time-rule', $time);
                }

                $id = sprintf('%d::%s', $organisation->getId(), $day['date']);
                $this->indexer->indexDocument('opening_time', $id, $day, 'libdir_schedules');
            }

            $this->indexer->flush();
        }

        $next = $blob + $skip;
        $response = $this->redirect()->toRoute(null, [], [
            'query' => [
                'auth' => $this->params()->fromQuery('auth'),
                'skip' => $next,
            ]
        ]);
        $response->getHeaders()->addHeaderLine('X-Index-Progress', $next);
        return $response;
    }

    // public function mobilestopsAction()
    // {
    //     $start = new DateTime;
    //     $end = new DateTime('+6 months');
    //
    //     $blob = 300;
    //     $skip = $this->params()->fromQuery('skip', 0);
    //
    //     $result = $this->getObjectManager()->createQueryBuilder()
    //         ->select('o')
    //         ->from(Organisation::class, 'o')
    //         ->addOrderBy('o.id')
    //         ->where('o.state = 1')
    //         ->andWhere('o.type = \'library\'')
    //         ->andWhere('o.branch_type = \'mobile\'')
    //         ->andWhere('o.id = 125156')
    //         ->setFirstResult($skip)
    //         ->setMaxResults($blob)
    //         ->getQuery()
    //         ->getResult();
    //
    //     $generator = $this->getServiceLocator()->get(LibrarySchedules::class);
    //     $schedules = $generator->generate(current($result), $start, $end);
    // }
}
