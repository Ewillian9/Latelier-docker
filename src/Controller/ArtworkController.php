<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\ArtworkImage;
use App\Entity\Comment;
use App\Form\ArtworkType;
use Symfony\UX\Turbo\TurboBundle;
use App\Form\CommentType;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use App\Repository\ArtworkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ArtworkController extends AbstractController
{
    public function redirectToPreferredLocale(Request $request): RedirectResponse
    {
        return new RedirectResponse('/' . substr($request->getPreferredLanguage(), 0, 2));
    }

    #[Route('/', name: 'app_artwork_index', methods: ['GET'])]
    public function index(Request $request, ArtworkRepository $artworkRepository): Response
    {
        $query = $request->query->get('q');
        if ($query) {
            $artworks = $artworkRepository->findByKeywords($query);
        } else {
            $artworks = $artworkRepository->findAll();
        }
        return $this->render('artwork/index.html.twig', [
            'artworks' => $artworks,
            'query' => $query,
        ]);
    }
    
    #[Route('artwork/new', name: 'app_artwork_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_ARTIST')) {
            $this->addFlash('error', 'You must login to do that!');
            return $this->redirectToRoute('app_login');
        }
        $artwork = new Artwork();
        $artwork->setArtist($this->getUser());
        for ($i = 0; $i < 6; $i++) {
            $artworkImage = new ArtworkImage();
            $artwork->addImage($artworkImage);
        }
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($artwork->getImages() as $image) {
                if ($image->getImageFile() === null) {
                    $artwork->removeImage($image);
                } else {
                    $image->setArtwork($artwork);
                    $em->persist($image);
                }
            }
            $em->persist($artwork);
            $em->flush();
            return $this->redirectToRoute('app_artwork_show', ['id' => $artwork->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('artwork/new.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }

    #[Route('artwork/{id}', name: 'app_artwork_show', methods: ['GET', 'POST'])]
    public function show(Artwork $artwork, Request $request, HubInterface $hub, EntityManagerInterface $em): Response
    {
        $form = null;
        if ($user = $this->getUser()) {
            $comment = new Comment();
            $comment->setArtwork($artwork);
            $comment->setUser($user);
            $form = $this->createForm(CommentType::class, $comment);
            $emptyForm = clone $form;
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($comment);
                $em->flush();
                $hub->publish(new Update(
                    'comment',
                    $this->renderBlock('comment/comment.stream.html.twig', 'create', [
                        'comment' => $form->getData([]),
                        'form' => $emptyForm,
                    ])
                ));
            }
        }
        return $this->render('artwork/show.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }

    #[Route('artwork/{id}/edit', name: 'app_artwork_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artwork $artwork, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $artwork->getArtist()) {
            $this->addFlash('error', 'No!');
            return $this->redirectToRoute('app_login');
        }

        $imagesToAdd = 8 - $artwork->getImages()->count();
        if ($imagesToAdd > 0) {
            for ($i = 0; $i < $imagesToAdd; $i++) {
                $artworkImage = new ArtworkImage();
                $artwork->addImage($artworkImage);
            }
        }
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($artwork->getImages() as $image) {
                if ($image->getImageFile() === null && $image->getId() === null) {
                    $artwork->removeImage($image);
                } elseif ($image->getImageFile() !== null) {
                    $image->setArtwork($artwork);
                    $em->persist($image);
                }
            }
            $em->flush();
            return $this->redirectToRoute('app_artwork_show', ['id' => $artwork->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('artwork/edit.html.twig', [
            'artwork' => $artwork,
            'form' => $form,
        ]);
    }

    #[Route('artwork/{id}/delete', name: 'app_artwork_delete', methods: ['POST'])]
    public function delete(Request $request, Artwork $artwork, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $artwork->getArtist()) {
            throw $this->createAccessDeniedException();
        }
        
        if ($this->isCsrfTokenValid('delete'.$artwork->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($artwork);
            $em->flush();
            $refererPath = parse_url($request->headers->get('referer'), PHP_URL_PATH);
            $route = $refererPath === '/profile/my-artworks' ? 'app_my_artworks' : 'app_artwork_index';
            return $this->redirectToRoute($route, [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash('error', 'Error when trying to delete.');
        }
    }
}
