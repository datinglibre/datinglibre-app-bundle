<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\UserInterestService;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserProfileViewController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private UserInterestService $userInterestService;

    public function __construct(
        ProfileRepository $profileRepository,
        UserInterestService $userInterestService
    )
    {
        $this->profileRepository = $profileRepository;
        $this->userInterestService = $userInterestService;
    }

    public function index(Uuid $userId)
    {
        $profile = $this->profileRepository->findProjectionByCurrentUser($this->getUser()->getId(), $userId);
        $interests = $this->userInterestService->findInterestsByUserId($userId);

        if ($profile === null || $profile->isBlockedByUser()) {
            throw $this->createNotFoundException();
        }

        return $this->render('@DatingLibreApp/user/search/view.html.twig', [
            'profile' => $profile,
            'interests' => $interests,
        ]);
    }
}
