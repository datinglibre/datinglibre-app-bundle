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
use Symfony\Component\Uid\Uuid;

class UserPasswordUpdateController extends AbstractController
{
    public function processPasswordReset(Request $request, UserService $userService, SessionInterface $session): Response
    {
        $userId = Uuid::fromString($request->get('userId'));
        $secret = $request->get('secret');

        $passwordUpdateForm = new PasswordUpdateForm();
        $passwordUpdateForm->setSecret($secret);
        $passwordUpdateForm->setUserId($userId->toRfc4122());

        $passwordUpdateFormType = $this->createForm(PasswordUpdateFormType::class, $passwordUpdateForm);
        $passwordUpdateFormType->handleRequest($request);

        if ($passwordUpdateFormType->isSubmitted() && $passwordUpdateFormType->isValid()) {
            if ($userService->updatePassword(
                Uuid::fromString($passwordUpdateForm->getUserId()),
                $passwordUpdateForm->getSecret(),
                $passwordUpdateForm->getPassword()
            )) {
                $session->getFlashBag()->add('success', 'user.password_updated');
            } else {
                $session->getFlashBag()->add('danger', 'user.password_update_failed');
            }

            return $this->redirectToRoute('user_login');
        }

        return $this->render('@DatingLibreApp/user/account/password_update.html.twig', [
            'passwordUpdateForm' => $passwordUpdateFormType->createView(),
        ]);
    }
}
