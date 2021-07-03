<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\UserSetting;
use DatingLibre\AppBundle\Form\UserSettingFormType;
use DatingLibre\AppBundle\Repository\UserRepository;
use DatingLibre\AppBundle\Repository\UserSettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAccountSettingController extends AbstractController
{
    private UserSettingRepository $userSettingRepository;
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, UserSettingRepository $userSettingRepository)
    {
        $this->userSettingRepository = $userSettingRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Request $request): Response
    {
        $userSetting = $this->getUserSetting();

        $userSettingFormType = $this->createForm(UserSettingFormType::class, $userSetting);
        $userSettingFormType->handleRequest($request);

        if ($userSettingFormType->isSubmitted() && $userSettingFormType->isValid()) {
            $this->userSettingRepository->save($userSetting);
            $this->addFlash('success', 'account.settings_updated');
            return $this->redirectToRoute('user_account_setting');
        }

        return $this->render(
            '@DatingLibreApp/user/account/settings.html.twig',
            [
                'userSettingsForm' => $userSettingFormType->createView()
            ]
        );
    }

    public function getUserSetting(): UserSetting
    {
        $userSetting = $this->userSettingRepository->findOneBy(['user' => $this->getUser()->getId()]) ?? new UserSetting();

        if ($userSetting->getUser() === null) {
            $userSetting->setUser($this->userRepository->findOneBy(['id' => $this->getUser()->getId()]));
        }
        return $userSetting;
    }
}
