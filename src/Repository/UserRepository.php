<?php

namespace App\Repository;

use App\Entity\User;
use App\Model\DbCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    public function getUsers(?DbCriteria $dbCriteria = null): array
    {
        $queryBuilder = $this->createQueryBuilder('u');
        if($dbCriteria && !is_null($dbCriteria->offset)){
            $queryBuilder->setFirstResult($dbCriteria->offset);
        }
        if($dbCriteria && !is_null($dbCriteria->limit)){
            $queryBuilder->setMaxResults($dbCriteria->limit);
        }
        if($dbCriteria && !is_null($dbCriteria->query)){
            $queryBuilder->where(
                $queryBuilder->expr()->like('u.email', ':query')
            )->orWhere(
                $queryBuilder->expr()->like('u.firstName', ':query')
            )->orWhere(
                $queryBuilder->expr()->like('u.lastName', ':query')
            );
            $queryBuilder->setParameter('query', '%'.$dbCriteria->query.'%');
        }

        $queryBuilder->orderBy('u.employmentDate', 'ASC');
        return $queryBuilder->getQuery()->getArrayResult();
    }
}
