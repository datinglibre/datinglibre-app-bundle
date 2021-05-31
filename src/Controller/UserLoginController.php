<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserLoginController extends AbstractController
{
    private bool $isDemo;

    public function __construct(bool $isDemo)
    {
        $this->isDemo = $isDemo;
    }

    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted(User::ADMIN)) {
            return $this->redirectToRoute('admin_events_index');
        }
        if ($authorizationChecker->isGranted(User::MODERATOR)) {
            return $this->redirectToRoute('moderator_profile_images');
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('user_search_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@DatingLibreApp/user/account/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'isDemo' => $this->isDemo
        ]);
    }
}
