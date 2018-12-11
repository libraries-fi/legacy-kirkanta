<?php

namespace Kirkanta\Ptv\Controller;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Kirkanta\Entity\Organisation;
use Kirkanta\Ptv\PtvManager;
use Zend\Mvc\Controller\AbstractActionController;

class ExportController extends AbstractActionController
{
    private $ptv;

    public static function create(ContainerInterface $container)
    {
        return new static($container->get(PtvManager::class));
    }

    public function __construct(PtvManager $ptv_manager)
    {
        $this->ptv = $ptv_manager;
    }

    public function demoAction()
    {
        $query = $this->ptv->getEntityManager()->getRepository(Organisation::class)->findBy([
            'type' => 'library',
            'branch_type' => ['library', 'main_library'],
            'city' => 16146, //Vaasa

            'id' => 85054
        ]);

        $mapper = new ServiceLocationMapper;

        foreach ($query as $item) {
            $doc = $mapper->convert($item);

            header('Content-Type: application/json');
            print(json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        exit();
    }

    public function exportAction()
    {
        $limit = 20;

        $em = $this->ptv->getEntityManager();

        // Use coalesce() to fetch unsynced entities first.
        $query = $em->getConnection()->prepare('
            SELECT o.id
            FROM organisations o
                INNER JOIN ptv_meta p
                    ON o.id = p.entity_id AND p.entity_type = \'organisation\'
            WHERE p.enabled = true AND o.modified > p.last_sync OR p.last_sync IS NULL
            ORDER BY coalesce(p.last_sync, \'2010-01-01\')
            LIMIT ?
        ');

        $query->execute([$limit]);
        $query->setFetchMode(\PDO::FETCH_COLUMN, 0);
        $eids = $query->fetchAll();

        $entities = $em->getRepository(Organisation::class)->findBy([
            'id' => $eids
        ]);

        $success = 0;
        $error = 0;

        foreach ($entities as $entity) {
            try {
                $this->ptv->sync($entity);
                $success++;
            } catch (Exception $e) {
                $error++;
            }
        }

        $em->flush();

        if ($error) {
            $message = sprintf('Failed to export %d out of %d entities. Check the ptv_meta table for logs.', $error, count($entities));
            $this->getResponse()->getStatusCode(500);
            $this->getResponse()->setContent($message);
            return $this->getResponse();
        }

        return $this->getResponse();
    }
}
