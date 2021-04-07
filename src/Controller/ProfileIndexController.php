<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use DatingLibre\AppBundle\Service\UserAttributeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProfileIndexController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private UserAttributeService $userAttributeService;
    private SuspensionService $suspensionService;

    public function __construct(
        ProfileRepository $profileRepository,
        UserAttributeService $userAttributeService,
        SuspensionService $suspensionService
    ) {
        $this->profileRepository = $profileRepository;
        $this->userAttributeService = $userAttributeService;
        $this->suspensionService = $suspensionService;
    }

    public function index()
    {
        $profile = $this->profileRepository->findProjection($this->getUser()->getId());
        $suspension = $this->suspensionService->findOpenByUserId($this->getUser()->getId());

        if ($profile === null) {
            $this->addFlash('warning', 'profile.incomplete');
            return new RedirectResponse($this->generateUrl('profile_edit'));
        }

        return $this->render('@DatingLibreApp/user/profile/index.html.twig', [
            'attributes' => $this->userAttributeService->getAttributesByUser($profile->getId()),
            'profile' => $profile,
            'suspension' => $suspension,
            'controller_name' => 'ProfileController',
        ]);
    }
}
