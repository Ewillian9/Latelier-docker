<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneByUsers(User $user, User $artist): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.client = :user AND c.artist = :artist')
            ->orWhere('c.client = :artist AND c.artist = :user')
            ->setParameter('user', $user)
            ->setParameter('artist', $artist)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
