<?php

namespace App\Repository;

use App\Entity\Artwork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artwork>
 */
class ArtworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artwork::class);
    }

    public function findByKeywords(string $query): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.title LIKE :q OR a.description LIKE :q OR a.keywords LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $query, ?string $sort): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.likes', 'l')
            ->addSelect('COUNT(l.id) AS HIDDEN likesCount')
            ->groupBy('a.id');

        if ($query) {
            $qb->andWhere('a.title LIKE :q OR a.description LIKE :q OR a.keywords LIKE :q')
            ->setParameter('q', '%' . $query . '%');
        }

        switch ($sort) {
            case 'likes_desc':
                $qb->orderBy('likesCount', 'DESC');
                break;
            case 'updated_asc':
                $qb->orderBy('a.updatedAt', 'ASC');
                break;
            case 'updated_desc':
                $qb->orderBy('a.updatedAt', 'DESC');
                break;
            case 'title_asc':
                $qb->orderBy('a.title', 'ASC');
                break;

            default:
                $qb->orderBy('a.updatedAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Artwork[] Returns an array of Artwork objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Artwork
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
