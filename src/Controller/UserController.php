<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArtworkRepository;
use App\Repository\CommentRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Repository\ConversationRepository;
use App\Repository\LikeRepository;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        if (!$user = $this->getUser()) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }

        $hasVisibleConversations = false;
        foreach ($user->getConversations() as $c) {
            if (!$c->isDeletedByClient()) {
                $hasVisibleConversations = true;
                break;
            }
        }

        if (!$hasVisibleConversations) {
            foreach ($user->getArtistConversations() as $c) {
                if (!$c->isDeletedByArtist()) {
                    $hasVisibleConversations = true;
                    break;
                }
            }
        }

        $visibleClientConversations = array_filter($user->getConversations()->toArray(), fn($c) => !$c->isDeletedByClient());
        $visibleArtistConversations = array_filter($user->getArtistConversations()->toArray(), fn($c) => !$c->isDeletedByArtist());
        $visibleCount = count($visibleClientConversations) + count($visibleArtistConversations);
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'hasVisibleConversations' => $hasVisibleConversations,
            'visibleConversationsCount' => $visibleCount,
        ]);
    }

    #[Route('/artist/{username}', name: 'app_artist_profile', methods: ['GET'])]
    public function publicProfile(UserRepository $userRepository, string $username): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user || !(in_array('ROLE_ARTIST', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()))) {
            throw $this->createNotFoundException('Artist not found');
        }

        return $this->render('user/public_profile.html.twig', [
            'artist' => $user,
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

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($plainPassword = $form->get('plainPassword')->getData()) {
                    $user->setPassword($hasher->hashPassword($user, $plainPassword));
                }
                if ($bio = $form->get('bio')->getData()) {
                    $user->setBio($bio);
                }
                if ($email = $form->get('email')->getData()) {
                    $user->setEmail($email);
                }
                if ($username = $form->get('username')->getData()) {
                    $user->setUsername($username);
                }
                $em->flush();
                return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
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
        $conversations = $cr->findVisibleForUser($user);

        return $this->render('user/my_conversations.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/profile/my-orders', name: 'app_my_orders', methods: ['GET'])]
    public function myOrders(OrderRepository $or): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }
        $orders = $user->getOrders();
        $artistOrders = $user->getArtistOrders();
        $allOrders = array_merge($orders->toArray(), $artistOrders->toArray());

        return $this->render('user/my_orders.html.twig', [
            'orders' => $allOrders
        ]);
    }

    #[Route('/profile/my-likes', name: 'app_my_likes', methods: ['GET'])]
    public function myLikes(LikeRepository $lr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You are not connected!');
            return $this->redirectToRoute('app_login');
        }
        $likes = $lr->findBy(['client' => $user]);

        return $this->render('user/my_likes.html.twig', [
            'likes' => $likes,
        ]);
    }
}
