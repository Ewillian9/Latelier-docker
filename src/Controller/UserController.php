<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArtworkRepository;
use App\Repository\CommentRepository;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        if (!$user = $this->getUser()) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plainPassword = $form->get('plainPassword')->getData()) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');
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
        if (!$user) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }
        $artworks = $ar->findBy(['artist' => $user]);

        return $this->render('user/my_artworks.html.twig', [
            'artworks' => $artworks,
        ]);
    }

    #[Route('/profile/my-comments', name: 'app_my_comments', methods: ['GET'])]
    public function myComments(CommentRepository $cr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }
        $comments = $cr->findBy(['commenter' => $user]);

        return $this->render('user/my_comments.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/profile/my-conversations', name: 'app_my_conversations', methods: ['GET'])]
    public function myConversations(ConversationRepository $cr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }
        $conversations = $cr->findByUser($user);

        return $this->render('user/my_conversations.html.twig', [
            'conversations' => $conversations,
        ]);
    }
}
