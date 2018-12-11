<?php

namespace Kirkanta\Export;

use Doctrine\ORM\EntityManagerInterface;
use Kirkanta\Entity\Organisation;

class AddressExporter
{
    private $em;

    public static function create(ServiceLocatorInterface $services)
    {
        return new static(EntityManager::class);
    }

    public function __construct(EntityManagerInterface $entities)
    {
        $this->em = $entities;
    }

    public function export(array $params)
    {
        $query = $this->compileQuery($params);
        $result = $query->getQuery()->getResult();
        return $result;
    }

    private function compileQuery(array $params)
    {
        $query = $this->em->createQueryBuilder()
            ->select([
                'o.name', 'o.email', 'o.coordinates',
                'a.street a_street', 'a.zipcode a_zipcode', 'c.name a_area',
                'm.street m_street', 'm.zipcode m_zipcode', 'm.area m_area', 'm.box_number m_box',
            ])
            ->from(Organisation::class, 'o')
            ->innerJoin('o.city', 'c')
            ->innerJoin('o.address', 'a')
            ->leftJoin('o.mail_address', 'm');

        if (!empty($params['type'])) {
            $query->andWhere('o.type IN (:types)')
                ->setParameter('types', $params['type']);
        }

        if (!empty($params['branch_type'])) {
            $query->andWhere('o.branch_type IN (:branch_types)')
                ->setParameter('branch_types', $params['branch_type']);
        }

        if (!empty($params['group_by'])) {
            $query->addOrderBy('c.name');
        }

        $query->addOrderBy('o.name');

        return $query;
    }
}
