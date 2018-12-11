<?php

namespace LegacyIndexing\Controller;

use DateTime;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use LegacyIndexing\Subscriber\LegacySchedules;
use Kirkanta\Entity\Organisation;
use KirkantaIndexing\Indexer;
use ScheduleGenerator\DayGenerator;
use ScheduleGenerator\Controller\ScheduleController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager;

class LegacyScheduleController extends AbstractActionController
{
    use ProvidesObjectManager;

    private $schedules;

    public static function create(ControllerManager $cm)
    {
        $services = $cm->getServiceLocator();
        return new static(
            $services->get('Doctrine\ORM\EntityManager'),
            new LegacySchedules($services)
        );
    }

    public function __construct(EntityManagerInterface $entity_manager, LegacySchedules $schedules)
    {
        $this->setObjectManager($entity_manager);
        $this->schedules = $schedules;
    }

    public function schedulesAction()
    {
        $start = new DateTime('-2 weeks');
        $end = new DateTime('+6 months');

        if ($start->format('N') != 1) {
            $start->sub(new DateInterval(sprintf('P%dD', $start->format('N') - 1)));
        }

        if ($end->format('N') != 7) {
            $end->add(new DateInterval(sprintf('P%dD', 7 - $end->format('N'))));
        }

        $blob = 300;
        $skip = $this->params()->fromQuery('skip', 0);

        $result = $this->getObjectManager()->createQueryBuilder()
            ->select('o')
            ->from(Organisation::class, 'o')
            ->addOrderBy('o.id')
            ->where('o.state = 1')

            // Sello
            // ->andWhere('o.id = 103405')

            // Pasila
            // ->andWhere('o.id = 84924')

            ->setFirstResult($skip)
            ->setMaxResults($blob)
            ->getQuery()
            ->getResult();

        foreach ($result as $organisation) {
            $this->schedules->processOrganisation($organisation, $start, $end);
        }

        // This is needed because cached schedules are stored in Organisation entity.
        $this->getObjectManager()->flush();

        $this->forward()->dispatch('ScheduleController', [
            'action' => 'schedules',
        ]);

        if (!count($result)) {
            exit('Finished' . PHP_EOL);
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
}
