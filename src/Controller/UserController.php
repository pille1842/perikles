<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @Route("/user/", name="user.")
 * @IsGranted("ROLE_USER_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index(UserRepository $userRepo): Response
    {
        $users = $userRepo->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("new", name="new")
     */
    public function new(UserPasswordEncoderInterface $passwordEncoder, Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('The new user was created successfully.'));

            return $this->redirectToRoute('user.index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("{id}", name="edit")
     */
    public function edit(UserPasswordEncoderInterface $passwordEncoder, Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('No user found for ID '.$id);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData() != '') {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('The changes were saved successfully.'));

            return $this->redirectToRoute('user.index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("{id}/delete", name="delete", methods={"POST","DELETE"})
     */
    public function delete(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new $this->createNotFoundException('No user found for ID '.$id);
        }

        if ($this->getUser()->getId() == $user->getId()) {
            // Don't allow the user to delete his own account
            $this->addFlash('danger', new TranslatableMessage('You cannot delete your own account.'));
            return $this->redirectToRoute('user.index');
        }

        $entityManager->remove($user);
        $this->addFlash('success', new TranslatableMessage('The user was removed successfully.'));
        $entityManager->flush();

        return $this->redirectToRoute('user.index');
    }
}
