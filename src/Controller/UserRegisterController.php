<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Form\RegistrationFormType;
use DatingLibre\AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserRegisterController extends AbstractController
{
    public function register(Request $request, SessionInterface $session, UserService $userService): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->signup($form->get('email')->getData(), $form->get('password')->getData());

            $session->getFlashBag()->add('success', 'user.registered');
            return $this->redirectToRoute('user_login');
        }

        return $this->render('@DatingLibreApp/user/register/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function terms(): Response
    {
        return $this->render('@DatingLibreApp/user/register/terms.html.twig');
    }
}
