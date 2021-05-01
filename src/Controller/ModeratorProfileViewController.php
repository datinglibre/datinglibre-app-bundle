<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\ProfileRepository;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorProfileViewController extends AbstractController
{
    private ProfileRepository $profileRepository;

    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function index(Uuid $userId)
    {
        $profile = $this->profileRepository->findProjection($userId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('@DatingLibreApp/moderator/profile/view.html.twig', [
            'profile' => $profile,
        ]);
    }
}
