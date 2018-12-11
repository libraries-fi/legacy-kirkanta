<?php

namespace KirkantaIndexing\Controller;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use KirkantaIndexing\Indexer;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager;

class IndexingController extends AbstractActionController
{
    use ProvidesObjectManager;

    public static function create(ControllerManager $cm)
    {
        $sm = $cm->getServiceLocator();
        return new static(
            $sm->get('Doctrine\ORM\EntityManager'),
            $sm->get('KirkantaIndexing\Indexer')
        );
    }

    public function __construct(EntityManagerInterface $entity_manager, Indexer $indexer)
    {
        $this->setObjectManager($entity_manager);
        $this->indexer = $indexer;
    }

    public function indexEntitiesAction()
    {
        // exit('DO INDEX');
        $alias = $this->params('type');
        $class = $this->entityInfo()->classForAlias($alias);
        $ids = array_filter(explode(',', $this->params('id')));

        $blob = 300;
        $skip = $this->params()->fromQuery('skip', 0);

        $this->indexer->beginBulk();

        $builder = $this->getObjectManager()->createQueryBuilder()
            ->select('o')
            ->addOrderBy('o.id')
            ->from($class, 'o')
            // ->andWhere('o.id = 84924')
            ->setFirstResult($skip)
            ->setMaxResults($blob);

        if ($ids) {
            $builder->where('o.id IN (:ids)');
            $builder->setParameter('ids', $ids);
        }

        if ($alias == 'organisation') {
            $builder->andWhere('o.state = 1');
        }

        $result = $builder->getQuery()->getResult();

        if (!count($result)) {
            // var_dump($result, $i, $i + $blob);
            exit('Finished' . PHP_EOL);
        }

        foreach ($result as $entity) {
            if (method_exists($entity, 'isPublished') and !$entity->isPublished()) {
                continue;
            }
            $this->indexer->index($entity);
        }
        $this->indexer->flush();
        $this->getObjectManager()->clear();

        $next = $blob + $skip;

        header(sprintf('Location: ?auth=%s&skip=%d', urlencode($this->params()->fromQuery('auth')), $next));
        header(sprintf('X-Index-Progress: %d', $next));
        exit;
    }
}
