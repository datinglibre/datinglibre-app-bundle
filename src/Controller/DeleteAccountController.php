<?php


namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\DeleteAccountFormType;
use DatingLibre\AppBundle\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeleteAccountController extends AbstractController
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
                $this->redirectToRoute('delete_my_user');
            }
        }

        return $this->render(
            '@DatingLibreApp/user/delete.html.twig',
            ['deleteAccountForm' => $deleteAccountForm->createView()]
        );
    }
}
