<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
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

//     /**
//      * @return User[] Returns an array of User objects
//      */
//    /*
//    public function findByExampleField($value)
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
//
//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findOneByEmailAndCreatedAt($email,$createdAt): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val1')
            ->andWhere('u.createdAt = :val2')
            ->setParameter('val1', $email)
            ->setParameter('val2', $createdAt)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findOneByEmailAndCreatedAtTimestamp($email,$createdAt): ?User
    {
        $date=new DateTime();
        $date=$date->setTimestamp($createdAt);
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val1')
            ->andWhere('u.createdAt = :val2')
            ->setParameter('val1', $email)
            ->setParameter('val2', $date)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    public function findOneByEmail($email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val1')
            ->setParameter('val1', $email)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

}
