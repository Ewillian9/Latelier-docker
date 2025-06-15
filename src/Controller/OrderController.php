<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order/create/{conversation}', name: 'app_order_create')]
    public function create(
        Conversation $conversation,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user || $user !== $conversation->getClient()) {
            $this->addFlash('error', 'Unauthorized access.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getArtist()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        if ($conversation->getOrder()) {
            $this->addFlash('info', 'An order already exists for this conversation.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getArtist()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        $order = new Order()
            ->setStatus('pending')
            ->setClient($user)
            ->setArtwork($conversation->getArtwork())
            ->setConversation($conversation);

        $em->persist($order);
        $em->flush();

        $this->addFlash('success', 'Order successfully created!');
        return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getArtist()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
    }

    #[Route('/order/{id}/accept', name: 'app_order_accept', methods: ['POST'])]
    public function accept(Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $conversation = $order->getConversation();

        if (!$user || $user !== $conversation->getArtist()) {
            $this->addFlash('error', 'Unauthorized access.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        if ($order->getStatus() !== 'pending') {
            $this->addFlash('info', 'Order is not pending.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        $order->setStatus('accepted');
        $em->flush();

        $this->addFlash('success', 'Order accepted.');
        return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
    }

    #[Route('/order/{id}/complete', name: 'app_order_complete', methods: ['POST'])]
    public function complete(Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $conversation = $order->getConversation();

        if (!$user || $user !== $conversation->getArtist()) {
            $this->addFlash('error', 'Unauthorized access.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        if ($order->getStatus() !== 'accepted') {
            $this->addFlash('info', 'Order must be accepted before completion.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        $order->setStatus('completed');
        $em->flush();

        $this->addFlash('success', 'Order marked as completed.');
        return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
    }

    #[Route('/order/{id}/cancel', name: 'app_order_cancel', methods: ['POST'])]
    public function cancel(Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $conversation = $order->getConversation();

        if (!$user || $user !== $conversation->getArtist()) {
            $this->addFlash('error', 'Unauthorized access.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        if ($order->getStatus() === 'completed') {
            $this->addFlash('info', 'Completed order cannot be cancelled.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
        }

        $order->setStatus('cancelled');
        $em->flush();

        $this->addFlash('success', 'Order marked as cancelled.');
        return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId(), 'artwork' => $conversation->getArtwork()->getId()]);
    }

}
