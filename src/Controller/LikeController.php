<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Artwork;
use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class LikeController extends AbstractController
{
    #[Route('/artwork/{id}/like', name: 'artwork_like', methods: ['POST'])]
    public function like(Artwork $artwork, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user->isVerified()) {
            throw new AccessDeniedException('You must be logged in to like artworks');
        }

        $existingLike = $em->getRepository(Like::class)->findOneBy([
            'artwork' => $artwork,
            'client' => $user,
        ]);

        if ($existingLike) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'You already liked this artwork'
            ], 400);
        }

        $like = new Like();
        $like->setArtwork($artwork);
        $like->setClient($user);

        $em->persist($like);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Artwork liked',
            'likesCount' => $artwork->getLikes(),
        ]);
    }

    #[Route('artwork/{id}/unlike', name: 'artwork_unlike', methods: ['POST'])]
    public function unlike(Artwork $artwork, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user->isVerified()) {
            throw new AccessDeniedException('You must be logged in to unlike artworks');
        }

        $existingLike = $em->getRepository(Like::class)->findOneBy([
            'artwork' => $artwork,
            'client' => $user,
        ]);

        if (!$existingLike) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'You have not liked this artwork'
            ], 400);
        }

        $em->remove($existingLike);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Artwork unliked.',
            'likesCount' => $artwork->getLikes(),
        ]);
    }
}