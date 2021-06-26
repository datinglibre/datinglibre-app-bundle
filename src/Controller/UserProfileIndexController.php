<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use DatingLibre\AppBundle\Service\UserAttributeService;
use DatingLibre\AppBundle\Service\UserInterestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Uid\Uuid;

class UserProfileIndexController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private UserAttributeService $userAttributeService;
    private SuspensionService $suspensionService;
    private UserInterestService $userInterestService;

    public function __construct(
        ProfileRepository $profileRepository,
        UserAttributeService $userAttributeService,
        UserInterestService $userInterestService,
        SuspensionService $suspensionService
    ) {
        $this->profileRepository = $profileRepository;
        $this->userAttributeService = $userAttributeService;
        $this->suspensionService = $suspensionService;
        $this->userInterestService = $userInterestService;
    }

    public function index()
    {
        $profile = $this->profileRepository->findProjection($this->getUser()->getId());
        $suspension = $this->suspensionService->findOpenByUserId($this->getUser()->getId());

        if ($profile === null) {
            $this->addFlash('warning', 'profile.incomplete');
            return new RedirectResponse($this->generateUrl('user_profile_edit'));
        }

        return $this->render('@DatingLibreApp/user/profile/index.html.twig', [
            'attributes' => $this->userAttributeService->getAttributesByUser($profile->getId()),
            'interests' => $this->userInterestService->findInterestsByUserId(Uuid::fromString($profile->getId())),
            'profile' => $profile,
            'suspension' => $suspension,
        ]);
    }
}
