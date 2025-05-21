<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArtworkRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        if (!$user = $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) throw $this->createAccessDeniedException();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plainPassword = $form->get('plainPassword')->getData()) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/my-artworks', name: 'app_my_artworks', methods: ['GET'])]
    public function myArtworks(ArtworkRepository $ar): Response
    {
        $user = $this->getUser();
        if (!$user) throw $this->createAccessDeniedException();
        
        $artworks = $ar->findBy(['artist' => $user]);

        return $this->render('user/my_artworks.html.twig', [
            'artworks' => $artworks,
        ]);
    }

    #[Route('/profile/my-comments', name: 'app_my_comments', methods: ['GET'])]
    public function myComments(CommentRepository $cr): Response
    {
        $user = $this->getUser();
        if (!$user) throw $this->createAccessDeniedException();

        $comments = $cr->findBy(['commenter' => $user]);

        return $this->render('user/my_comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
