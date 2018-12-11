<?php

namespace Kirkanta\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Kirkanta\Entity\ServiceType;
use Zend\Mvc\Controller\AbstractActionController;

class ServiceTypeController extends AbstractActionController
{
    private $em;

    public static function create(ContainerInterface $container)
    {
        return new static($container->get('Doctrine\ORM\EntityManager'));
    }

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function usageAction()
    {
        $id = (int)$this->params('id');
        $type = $this->em->getRepository(ServiceType::class)->findOneById($id);
        $services = $type->getServices()->toArray();
        // $services = $this->em->getRepository(Service::class)->findBy(['template' => $id]);

        usort($services, function($a, $b) {
            $a = $a->getOrganisation() ? $a->getOrganisation()->getName() : null;
            $b = $b->getOrganisation() ? $b->getOrganisation()->getName() : null;

            return strcasecmp($a, $b);
        });

        return [
            'service_type' => $type,
            'services' => $services,
        ];
    }
}
