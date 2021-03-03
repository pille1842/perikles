<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Vote;
use App\Form\VoteType;
use App\Service\VotingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VotingController extends AbstractController
{
    /**
     * @Route("/vote/{id}", name="vote")
     */
    public function vote(VotingService $votingService, Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket || !$ticket->getValid()) {
            return $this->render('voting/invalidticket.html.twig', [], new Response('', 404));
        }

        $poll = $ticket->getPoll();
        $vote = new Vote();
        $vote->setPoll($poll);
        $form = $this->createForm(VoteType::class, $vote, [
            'poll' => $poll,
            'passcode' => $ticket->getPasscode(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();

            // Invalidate the ticket...
            $ticket->setValid(false);
            // ... and store the hashed passcode on the vote for later validation of vote tallying
            $vote->setPasscode(hash('sha256', $ticket->getPasscode()));
            $entityManager->persist($ticket);
            $entityManager->persist($vote);

            $entityManager->commit();
            $entityManager->flush();

            return $this->redirectToRoute('vote_success');
        }

        return $this->render('voting/vote.html.twig', [
            'vote' => $vote,
            'poll' => $poll,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/success", name="vote_success")
     */
    public function success(): Response
    {
        return $this->render('voting/success.html.twig');
    }
}
