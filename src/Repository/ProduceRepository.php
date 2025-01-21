<?php

namespace App\Repository;

use App\Entity\Produce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produce>
 *
 * @method Produce|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produce|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produce[]    findAll()
 * @method Produce[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produce::class);
    }

    /**
     * @param $externalId
     * @return Produce|null Returns an array of Produce objects
     */
    public function findByExternalId($externalId): ?Produce
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.externalId = :externalId')
            ->setParameter('externalId', $externalId)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    public function findOneBySomeField($value): ?Produce
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
