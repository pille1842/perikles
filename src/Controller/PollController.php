<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Form\PollType;
use App\Repository\PollRepository;
use App\Service\VotingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/poll")
 * @IsGranted("ROLE_USER")
 */
class PollController extends AbstractController
{
    /**
     * @Route("/", name="poll_index", methods={"GET"})
     */
    public function index(PollRepository $pollRepository): Response
    {
        return $this->render('poll/index.html.twig', [
            'polls' => $pollRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="poll_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $poll = new Poll();
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($poll);
            $entityManager->flush();

            return $this->redirectToRoute('poll_index');
        }

        return $this->render('poll/new.html.twig', [
            'poll' => $poll,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="poll_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Poll $poll): Response
    {
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('poll_index');
        }

        return $this->render('poll/edit.html.twig', [
            'poll' => $poll,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="poll_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Poll $poll): Response
    {
        if ($this->isCsrfTokenValid('delete'.$poll->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($poll);
            $entityManager->flush();
        }

        return $this->redirectToRoute('poll_index');
    }

    /**
     * @Route("/{id}/start", name="poll_start", methods={"POST"})
     */
    public function start(VotingService $votingService, Request $request, Poll $poll): Response
    {
        if ($this->isCsrfTokenValid('start'.$poll->getId(), $request->request->get('_token'))) {
            $votingService->startVote($poll);

            $entityManager = $this->getDoctrine()->getManager();
            $poll->setStarted(true);
            $entityManager->persist($poll);
            $entityManager->flush();
        }

        return $this->redirectToRoute('poll_index');
    }

    /**
     * @Route("/{id}/stop", name="poll_stop", methods={"POST"})
     */
    public function stop(VotingService $votingService, Request $request, Poll $poll): Response
    {
        if ($this->isCsrfTokenValid('stop'.$poll->getId(), $request->request->get('_token'))) {
            $votingService->stopVote($poll);

            $entityManager = $this->getDoctrine()->getManager();
            $poll->setStopped(true);
            $entityManager->persist($poll);
            $entityManager->flush();
        }

        return $this->redirectToRoute('poll_index');
    }
}
