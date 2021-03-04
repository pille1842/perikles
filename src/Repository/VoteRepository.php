<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    /**
     * @return Vote Returns a Vote object
     */
    public function findOneByPasscodeHash(string $hash, Poll $poll): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.passcode = :val')
            ->andWhere('v.poll = :poll')
            ->setParameter('val', $hash)
            ->setParameter('poll', $poll)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
