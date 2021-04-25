<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTokenController extends AbstractController
{
    public function processConfirm(Request $request, UserService $userService, string $secret): Response
    {
        $userId = $request->get('userId');

        if ($userService->enable($userId, $secret)) {
            $this->addFlash('success', 'user.confirmed');
            return new RedirectResponse($this->generateUrl('login'));
        }

        $this->addFlash('danger', 'user.confirmation_failed');
        return new RedirectResponse($this->generateUrl('login'));
    }
}
