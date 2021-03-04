<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Ticket;
use App\Entity\Vote;
use App\Form\VoteType;
use App\Service\VotingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\NotBlank;

class VotingController extends AbstractController
{
    /**
     * @Route("/vote/{id}", name="vote", methods={"GET", "POST"})
     */
    public function vote(Request $request, int $id): Response
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
            $vote->setPasscode(hash('sha256', $form->get('passcode')->getData()));
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
     * @Route("/success", name="vote_success", methods={"GET"})
     */
    public function success(): Response
    {
        return $this->render('voting/success.html.twig');
    }

    /**
     * @Route("/result/{id}", name="poll_result", methods={"GET", "POST"})
     */
    public function result(VotingService $votingService, Request $request, Poll $poll): Response
    {
        if (!$poll->getStopped()) {
            return $this->render('voting/invalidpoll.html.twig', [], new Response('', 404));
        }

        $result = $votingService->calculateResult($poll);
        $form = $this->createFormBuilder([])
            ->add('passcode', TextType::class, [
                'constraints' => new NotBlank(),
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passcode = $form->get('passcode')->getData();
            $hash = hash('sha256', $passcode);
            $entityManager = $this->getDoctrine()->getManager();
            $vote = $entityManager->getRepository(Vote::class)->findOneByPasscodeHash($hash, $poll);
            if ($vote) {
                $this->addFlash(
                    'success', new TranslatableMessage(
                        'Congratulations! Your vote for "{{ option }}" was properly counted.',
                        ['{{ option }}' => $vote->getVotefor()->getLabel()]
                    )
                );
            } else {
                $this->addFlash('danger', 'Uh-oh. Your vote could not be found in the database. Something is wrong.');
            }
            return $this->redirectToRoute('poll_result', ['id' => $poll->getId()]);
        }

        return $this->render('voting/result.html.twig', [
            'poll' => $poll,
            'result' => $result,
            'form' => $form->createView(),
        ]);
    }
}
