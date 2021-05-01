<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\DeleteAccountFormType;
use DatingLibre\AppBundle\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserAccountDeleteController extends AbstractController
{
    public function delete(Request $request, UserService $userService)
    {
        $deleteAccountForm = $this->createForm(DeleteAccountFormType::class);
        $deleteAccountForm->handleRequest($request);

        if ($deleteAccountForm->isSubmitted() && $deleteAccountForm->isValid()) {
            if ($userService->deleteByPassword(
                $this->getUser()->getId(),
                $deleteAccountForm->getData()['password']
            )) {
                $this->get("security.token_storage")->setToken(null);
                return $this->redirectToRoute('logout');
            } else {
                $this->addFlash('danger', 'account.incorrect_password');
                $this->redirectToRoute('user_account_delete');
            }
        }

        return $this->render(
            '@DatingLibreApp/user/account/delete.html.twig',
            ['deleteAccountForm' => $deleteAccountForm->createView()]
        );
    }
}
