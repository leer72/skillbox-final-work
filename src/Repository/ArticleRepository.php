<?php

namespace App\Repository;

use DateTime;
use App\Entity\User;
use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function add(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllByUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCreatedAt(DateTime $period, User $user)
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :user')
            ->setParameter('user', $user->getId())
            ->andWhere('a.createdAt > :period')
            ->setParameter('period', $period)
            ->getQuery()
            ->getResult()
        ;
    }
}
