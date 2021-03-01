<?php

namespace App\Controller;

use App\Entity\Voter;
use App\Form\VoterType;
use App\Repository\VoterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/voter")
 * @IsGranted("ROLE_VOTER_ADMIN")
 */
class VoterController extends AbstractController
{
    /**
     * @Route("/", name="voter_index", methods={"GET"})
     */
    public function index(VoterRepository $voterRepository): Response
    {
        return $this->render('voter/index.html.twig', [
            'voters' => $voterRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="voter_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $voter = new Voter();
        $form = $this->createForm(VoterType::class, $voter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($voter);
            $entityManager->flush();

            return $this->redirectToRoute('voter_index');
        }

        return $this->render('voter/new.html.twig', [
            'voter' => $voter,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="voter_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Voter $voter): Response
    {
        $form = $this->createForm(VoterType::class, $voter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('voter_index');
        }

        return $this->render('voter/edit.html.twig', [
            'voter' => $voter,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voter_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Voter $voter): Response
    {
        if ($this->isCsrfTokenValid('delete'.$voter->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($voter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('voter_index');
    }
}
