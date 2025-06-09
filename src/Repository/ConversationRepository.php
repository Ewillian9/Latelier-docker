<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Artwork;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneByUsersAndArtwork(User $userA, User $userB, Artwork $artwork): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.artwork = :artwork')
            ->andWhere('(c.client = :userA AND c.artist = :userB) OR (c.client = :userB AND c.artist = :userA)')
            ->setParameter('userA', $userA)
            ->setParameter('userB', $userB)
            ->setParameter('artwork', $artwork)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.client = :user OR c.artist = :user')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
