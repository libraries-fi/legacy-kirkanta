<?php

namespace Kirkanta\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Kirkanta\Entity\Consortium;

class OrganisationRepository extends EntityRepository
{
    /**
     * NOTE: Parameter naming should be identical to EntityRepository::findBy in order to remain
     * compatible with Doctrine's reflection-based argument resolving.
     */
    public function findByConsortium(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (empty($criteria['consortium'])) {
            throw new Exception('This method requires key \'consortium\' in $criteria');
        }

        $result = $this->createQueryBuilder('o')
            ->select('o.id')
            ->innerJoin('o.city', 'c')
            ->where('(o.force_no_consortium = false AND o.consortium IS NULL AND c.consortium = :cid)')
            ->orWhere('(o.force_no_consortium = false AND o.consortium = :cid)')
            ->setParameter('cid', $criteria['consortium'])
            ->getQuery()
            ->getResult();

        $ids = array_map(function($row) { return $row['id']; }, $result);

        unset($criteria['consortium']);
        $criteria['id'] = $ids;

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}
