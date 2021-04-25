<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\PasswordResetFormType;
use DatingLibre\AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function strtolower;

class UserPasswordResetController extends AbstractController
{
    public function password(Request $request, SessionInterface $session, UserService $userService): Response
    {
        $passwordResetFormType = $this->createForm(PasswordResetFormType::class);
        $passwordResetFormType->handleRequest($request);

        if ($passwordResetFormType->isSubmitted() && $passwordResetFormType->isValid()) {
            $userService->resetPassword(strtolower($passwordResetFormType->getData()['email']));

            $session->getFlashBag()->add('success', 'user.password_reset_email_sent');

            return $this->redirectToRoute('login');
        }

        return $this->render('@DatingLibreApp/user/account/password_reset.html.twig', [
            'passwordResetForm' => $passwordResetFormType->createView(),
        ]);
    }
}
