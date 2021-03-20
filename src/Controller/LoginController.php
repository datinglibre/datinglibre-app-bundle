<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    private bool $isDemo;

    public function __construct(bool $isDemo)
    {
        $this->isDemo = $isDemo;
    }

    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authChecker): Response
    {
        if ($authChecker->isGranted(User::ADMIN)) {
            return $this->redirectToRoute('events_index');
        }
        if ($authChecker->isGranted(User::MODERATOR)) {
            return $this->redirectToRoute('moderate_profile_images');
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('search');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@DatingLibreApp/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'isDemo' => $this->isDemo
        ]);
    }
}
