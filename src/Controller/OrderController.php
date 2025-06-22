<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order/create/{conversation}', name: 'app_order_create', methods: ['POST'])]
    public function create(Request $request, Conversation $conversation, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $otherId = $conversation->getArtist() === $user ? $conversation->getClient()->getId()->toString() : $conversation->getArtist()->getId()->toString();
        $artwork = $conversation->getArtwork();

        if (!$user || $user !== $conversation->getClient()) {
            $this->addFlash('error', 'Only the client can initiate an order');
            return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $artwork->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($conversation->getOrder()) {
            $this->addFlash('info', 'An order already exists for this conversation');
            return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $artwork->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('create'.$conversation->getId()->toString(), $request->getPayload()->getString('_token'))) {
            $order = new Order()
                ->setStatus('pending')
                ->setClient($user)
                ->setArtist($conversation->getArtist())
                ->setArtwork($artwork)
                ->setConversation($conversation);

            $em->persist($order);
            $em->flush();
            $this->addFlash('success', 'Order successfully created!');
        } else {
            $this->addFlash('error', 'Something when wrong, please reload the page');
        }
        return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $artwork->getId()->toString()], Response::HTTP_SEE_OTHER);
    }

    #[Route('order/delete/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user || ($user !== $order->getClient() && $user !== $order->getArtist())) {
            $this->addFlash('error', 'Only participants can delete order');
            return $this->redirectToRoute('app_my_orders', [], Response::HTTP_SEE_OTHER);
        }

        if ($order->getStatus() !== 'pending' && $order->getStatus() !== 'completed' && $order->getStatus() !== 'cancelled') {
            $this->addFlash('error', 'Only pending, completed or cancelled orders can be deleted');
            return $this->redirectToRoute('app_my_orders', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$order->getId()->toString(), $request->getPayload()->getString('_token'))) {
            $conversation = $order->getConversation();
            $conversation->setOrder(null);

            $em->remove($order);
            $em->flush();

            $this->addFlash('success', 'Order successfully deleted');
        } else {
            $this->addFlash('error', 'Something when wrong, please reload the page');
        }
        return $this->redirectToRoute('app_my_orders', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/order/{id}/accept', name: 'app_order_accept', methods: ['POST'])]
    public function accept(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $conversation = $order->getConversation();

        if (!$user || $user !== $conversation->getArtist()) {
            $this->addFlash('error', 'Only the owner of the artwork can accept the related order');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId()->toString(), 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($order->getStatus() !== 'pending') {
            $this->addFlash('info', 'Only pending orders can be accepted');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId()->toString(), 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('accept'.$order->getId()->toString(), $request->getPayload()->getString('_token'))) {
            $order->setStatus('accepted');
            $em->flush();

            $this->addFlash('success', 'Order accepted!');
        } else {
            $this->addFlash('error', 'Something when wrong, please reload the page');
        }
        return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId()->toString(), 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/order/{id}/complete', name: 'app_order_complete', methods: ['POST'])]
    public function complete(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $conversation = $order->getConversation();

        if (!$user || $user !== $conversation->getArtist()) {
            $this->addFlash('error', 'Only the owner of the artwork can complete the related order');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId()->toString(), 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($order->getStatus() !== 'accepted') {
            $this->addFlash('info', 'Order must be accepted before completion.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId()->toString(), 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('complete'.$order->getId()->toString(), $request->getPayload()->getString('_token'))) {
            $order->setStatus('completed');
            $em->flush();

            $this->addFlash('success', 'Order is completed!');
        } else {
            $this->addFlash('error', 'Something when wrong, please reload the page');
        }
        return $this->redirectToRoute('app_conversation', ['recipient' => $conversation->getClient()->getId()->toString(), 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/order/{id}/cancel', name: 'app_order_cancel', methods: ['POST'])]
    public function cancel(Request $request, Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $conversation = $order->getConversation();
        $otherId = $conversation->getArtist() === $user ? $conversation->getClient()->getId()->toString() : $conversation->getArtist()->getId()->toString();

        if (!$user || ($user !== $conversation->getArtist() && $user !== $conversation->getClient())) {
            $this->addFlash('error', 'Only the owner of the artwork can cancel the related order');
            return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($order->getStatus() === 'completed') {
            $this->addFlash('info', 'Completed order cannot be cancelled');
            return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($user === $order->getClient() && $order->getStatus() === 'accepted') {
            $this->addFlash('info', 'Completed order cannot be cancelled.');
            return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('cancel'.$order->getId()->toString(), $request->getPayload()->getString('_token'))) {
            $order->setStatus('cancelled');
            $em->flush();

            $this->addFlash('success', 'Order cancelled successfully, you can delete it to create a new one');
        } else {
            $this->addFlash('error', 'Something when wrong, please reload the page');
        }
        return $this->redirectToRoute('app_conversation', ['recipient' => $otherId, 'artwork' => $conversation->getArtwork()->getId()->toString()], Response::HTTP_SEE_OTHER);
    }

}
