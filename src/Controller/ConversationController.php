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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class ConversationController extends AbstractController
{
    #[Route('/conversation/{recipient}', name: 'app_conversation', methods: ['GET', 'POST'])]
    public function show(ConversationRepository $cr, User $recipient, Request $request, EntityManagerInterface $em, HubInterface $hub): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->isVerified()) {
            $this->addFlash('error', 'You must login to do that');
            return $this->redirectToRoute('app_login');
        }

        $conversationId = $request->query->get('conversation');
        $artworkId = $request->query->get('artwork');
        if ($conversationId) {
            $conversation = $cr->find($conversationId);

            if (!$conversation || !$conversation->isParticipant($user)) {
                $this->addFlash('error', 'Conversation not found or access denied');
                return $this->redirectToRoute('app_artwork_index');
            }

            $artwork = $conversation->getArtwork();
        } elseif ($artworkId) {
            $artwork = $em->getRepository(Artwork::class)->find($artworkId);

            if (!$artwork) {
                $this->addFlash('error', 'The artwork is missing to create a conversation');
                return $this->redirectToRoute('app_artwork_index');
            }

            if ($user === $recipient) {
                $this->addFlash('error', 'You cannot create a conversation with yourself');
                return $this->redirectToRoute('app_artwork_index');
            }

            $conversation = $cr->findOneByUsersAndArtwork($user, $recipient, $artwork);

            if (!$conversation) {
                if ($recipient !== $artwork->getArtist()) {
                    $this->addFlash('error', 'Artist/Client/Artwork missmatch');
                    return $this->redirectToRoute('app_artwork_index');
                }

                $conversation = new Conversation()
                    ->setArtwork($artwork)
                    ->setClient($user)
                    ->setArtist($recipient);
            }
        } else {
            $this->addFlash('error', 'Missing artwork or conversation ID');
            return $this->redirectToRoute('app_artwork_index');
        }

        $ids = [$user->getId()->toString(), $recipient->getId()->toString()];
        sort($ids);
        $topic = sprintf('%s%s%s', $ids[0], $ids[1], $artwork?->getId()?->toString() ?? $conversation->getId()->toString());

        $message = new Message()
            ->setSender($user);

        $form = $this->createForm(MessageType::class, $message);
        $emptyForm = clone $form;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $conversation->getId()) {
                $em->persist($conversation);
                $em->flush();
            }
            
            $message->setConversation($conversation);

            if ($conversation->getClient() === $user) {
                $conversation->setIsDeletedByArtist(false);
            } elseif ($conversation->getArtist() === $user) {
                $conversation->setIsDeletedByClient(false);
            }

            $em->persist($message);
            $em->flush();

            foreach ([$user, $recipient] as $recipientUser) {
                $hub->publish(new Update(
                    $topic . $recipientUser->getId()->toString(),
                    $this->renderBlock('conversation/message.stream.html.twig', 'create', [
                        'conversation' => $conversation,
                        'message' => $message,
                        'user' => $recipientUser === $user ? $user : $recipientUser,
                        'form' => $recipientUser === $user ? $emptyForm : null
                    ])
                ));
            }
        }

        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
            'artwork' => $artwork,
            'messages' => $conversation->getMessages(),
            'form' => $form,
            'topic' => $topic
        ]);
    }

    #[Route('/conversation/{id}/delete', name: 'app_conversation_delete', methods: ['POST'])]
    public function delete(Request $request, Conversation $conversation, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->isVerified()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$conversation->getId()->toString(), $request->getPayload()->getString('_token'))) {

            if ($user === $conversation->getClient()) {
                $conversation->setIsDeletedByClient(true);
            } elseif ($user === $conversation->getArtist()) {
                $conversation->setIsDeletedByArtist(true);
            } else {
                throw $this->createAccessDeniedException();
            }

            if ($conversation->isDeletedByBoth()) {
                $em->remove($conversation);
            }
            $em->flush();
            
            $this->addFlash('success', 'Conversation deleted successfully');
        } else {
            $this->addFlash('error', 'Error when deleting conversation, try again');
        }
        return $this->redirectToRoute('app_my_conversations', [], Response::HTTP_SEE_OTHER);
    }

}
