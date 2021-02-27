<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\PasswordUpdateForm;
use DatingLibre\AppBundle\Form\PasswordUpdateFormType;
use DatingLibre\AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PasswordUpdateController extends AbstractController
{
    public function processPasswordReset(Request $request, UserService $userService, SessionInterface $session): Response
    {
        $userId = $request->get('userId');
        $secret = $request->get('secret');

        $passwordUpdateForm = new PasswordUpdateForm();
        $passwordUpdateForm->setSecret($secret);
        $passwordUpdateForm->setUserId($userId);

        $passwordUpdateFormType = $this->createForm(PasswordUpdateFormType::class, $passwordUpdateForm);
        $passwordUpdateFormType->handleRequest($request);

        if ($passwordUpdateFormType->isSubmitted() && $passwordUpdateFormType->isValid()) {
            if ($userService->updatePassword(
                $passwordUpdateForm->getUserId(),
                $passwordUpdateForm->getSecret(),
                $passwordUpdateForm->getPassword()
            )) {
                $session->getFlashBag()->add('success', 'user.password_updated');
            } else {
                $session->getFlashBag()->add('danger', 'user.password_update_failed');
            }

            return $this->redirectToRoute('login');
        }

        return $this->render('@DatingLibreApp/user/password_update.html.twig', [
            'controller_name' => 'update_password',
            'passwordUpdateForm' => $passwordUpdateFormType->createView(),
        ]);
    }
}
