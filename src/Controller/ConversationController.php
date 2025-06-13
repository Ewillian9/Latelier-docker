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
    #[Route('/conversation/{recipient}', name: 'app_conversation', methods: ['GET', 'POST'])]
    public function show(ConversationRepository $cr, MessageRepository $mr, User $recipient, Request $request, EntityManagerInterface $em, HubInterface $hub): Response
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

        if ($user === $recipient) {
            $this->addFlash('info', 'You cannot create a conversation with yourself!');
            return $this->redirectToRoute('app_artwork_index');
        }

        $conversation = $cr->findOneByUsersAndArtwork($user, $recipient, $artwork);
        
        if (!$conversation) {
            $conversation = new Conversation()
                ->setArtwork($artwork)
                ->setClient($user)
                ->setArtist($recipient);
        }

        $ids = [$artwork->getId(), $user->getId(), $recipient->getId()];
        sort($ids);
        $topic = sprintf('%d%d%d', $ids[0], $ids[1], $ids[2]);

        $message = new Message()->setSender($user);

        $form = $this->createForm(MessageType::class, $message);
        $emptyForm = clone $form;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $conversation->getId()) {
                $em->persist($conversation);
                $em->flush();
            }
            
            $message->setConversation($conversation);
            $em->persist($message);
            $em->flush();

            foreach ([$user, $recipient] as $recipient) {
                $hub->publish(new Update(
                    $topic . $recipient->getId(),
                    $this->renderBlock('conversation/message.stream.html.twig', 'create', [
                        'conversation' => $conversation,
                        'message' => $message,
                        'user' => $recipient === $user ? $user : $recipient,
                        'form' => $recipient === $user ? $emptyForm : null
                    ])
                ));
            }
        }

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $conversation->getMessages(),
            'form' => $form,
            'topic' => $topic
        ]);
    }
}
