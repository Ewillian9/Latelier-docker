<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Artwork;
use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class ConversationController extends AbstractController
{
    #[Route('/conversation/{artist}', name: 'app_conversation', methods: ['GET', 'POST'])]
    public function show(ConversationRepository $cr, MessageRepository $mr, User $artist, Request $request, EntityManagerInterface $em, HubInterface $hub): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('info', 'You must login to do that!');
            return $this->redirectToRoute('app_login');
        }
        
        $artworkId = $request->query->get('artwork');
        $artwork = $em->getRepository(Artwork::class)->find($artworkId);
        if (!$artwork) {
            $this->addFlash('error', 'The artwork is missing to create a conversation!');
            return $this->redirectToRoute('app_artwork_index');
        }
        $conversation = $cr->findOneByUsersAndArtwork($user, $artist, $artwork);
        
        if (!$conversation) {
            if ($user === $artist) {
                $this->addFlash('info', 'You cannot create a conversation with yourself!');
                return $this->redirectToRoute('app_artwork_index');
            }
            $conversation = new Conversation();
            $conversation->setArtwork($artwork);
            $conversation->setClient($user);
            $conversation->setArtist($artist);

            $em->persist($conversation);
            $em->flush();
        }

        $message = new Message();
        $message->setSender($user);
        $message->setConversation($conversation);
        $form = $this->createForm(MessageType::class, $message);
        $emptyForm = clone $form;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $em->flush();

            $receiver = $conversation->getOtherParticipant($this->getUser());

            $hub->publish(new Update(
                sprintf('conversation-%d-%d', $conversation->getId(), $message->getSender()->getId()),
                $this->renderBlock('conversation/message.stream.html.twig', 'create', [
                    'conversation' => $conversation,
                    'message' => $message,
                    'user' => $message->getSender(),
                    'form' => $emptyForm
                ])
            ));

            $hub->publish(new Update(
                sprintf('conversation-%d-%d', $conversation->getId(), $receiver->getId()),
                $this->renderBlock('conversation/message.stream.html.twig', 'create', [
                    'conversation' => $conversation,
                    'message' => $message,
                    'user' => $receiver,
                    'form' => $emptyForm
                ])
            ));
        }

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $conversation->getMessages(),
            'form' => $form
        ]);
    }
}
