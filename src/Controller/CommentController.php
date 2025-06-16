<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    #[Route('comment/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, HubInterface $hub, EntityManagerInterface $em): Response
    {
        if ($comment->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'You dont have the privileges to do that');
            return $this->redirectToRoute('app_artwork_index');
        }

        $form = $this->createForm(CommentType::class, $comment);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $artwork = $comment->getArtwork();
                $em->flush();

                $hub->publish(new Update(
                    'comment' . $artwork->getId(),
                    $this->renderBlock('comment/comment.stream.html.twig', 'update', [
                        'id' => $comment->getId(),
                        'comment' => $form->getData([])
                    ])
                ));
            }
        }

        return $this->render('comment/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('comment/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, HubInterface $hub, CommentRepository $cr, EntityManagerInterface $em): Response
    {
        $user = $comment->getUser();

        if ($user !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $commentId = $comment->getId();
            $artwork = $comment->getArtwork();
            $em->remove($comment);
            $em->flush();
            
            $hub->publish(new Update(
                'comment' . $artwork->getId(),
                $this->renderBlock('comment/comment.stream.html.twig', 'delete', [
                    'id' => $commentId,
                    'remainingCommentsCount' => $cr->count(['artwork' => $comment->getArtwork()]),
                    'remainingUserCommentsCount' => $cr->count(['commenter' => $user]),
                ])
            ));
            $hub->publish(new Update(
                'comment',
                $this->renderBlock('comment/comment.stream.html.twig', 'delete', [
                    'id' => $commentId,
                    'remainingCommentsCount' => $cr->count(['artwork' => $comment->getArtwork()]),
                    'remainingUserCommentsCount' => $cr->count(['commenter' => $user]),
                ])
            ));
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
