<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Artwork;
use App\Form\CommentType;
use Symfony\UX\Turbo\TurboBundle;
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
    #[Route('comment/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('comment/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('comment/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, Artwork $artwork, EntityManagerInterface $em): Response
    {
        if ($comment->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) throw $this->createAccessDeniedException();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_artwork_show', ['id' => $artwork->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('comment/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, HubInterface $hub, CommentRepository $cr, EntityManagerInterface $em): Response
    {
        if ($comment->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) throw $this->createAccessDeniedException();

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $commentId = $comment->getId();
            $commentUser = $comment->getUser();
            $em->remove($comment);
            $em->flush();
            $hub->publish(new Update(
                'comment',
                $this->renderBlock('comment/delete_comment.stream.html.twig', 'delete_comment', [
                    'commentId' => $commentId,
                    'remainingCommentsCount' => $cr->count(['artwork' => $comment->getArtwork()]),
                    'remainingUserCommentsCount' => $cr->count(['commenter' => $commentUser]),
                    ]
                )
            ));
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
