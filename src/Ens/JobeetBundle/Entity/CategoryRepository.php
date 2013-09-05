<?php

namespace Ens\JobeetBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends EntityRepository
{
    public function getWithJobs()
    {
        $querybuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.jobs', 'j')
                ->addSelect('j')
                ->andWhere('j.expires_at > :date')
                ->setParameter('date', date('Y-m-d H:i:s', time()))
                ->andWhere('j.isActivated = :activated')
                ->setParameter('activated', 1);

        return $querybuilder->getQuery()->getResult();
    }

}
