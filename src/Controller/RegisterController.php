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

class RegisterController extends AbstractController
{
    public function register(Request $request, SessionInterface $session, UserService $userService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->signup($user, $form->get('password')->getData());

            $session->getFlashBag()->add('success', 'user.registered');
            return $this->redirectToRoute('login');
        }

        return $this->render('@DatingLibreApp/register/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function terms(): Response
    {
        return $this->render('@DatingLibreApp/register/terms.html.twig');
    }
}
