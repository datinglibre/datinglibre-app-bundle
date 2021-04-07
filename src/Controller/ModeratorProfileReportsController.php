<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\ReportService;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorProfileReportsController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private ReportService $reportService;

    public function __construct(ProfileRepository $profileRepository, ReportService $reportService)
    {
        $this->profileRepository = $profileRepository;
        $this->reportService = $reportService;
    }

    public function index(Uuid $userId)
    {
        $profile = $this->profileRepository->findProjection($userId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('@DatingLibreApp/moderator/profile/reports.html.twig', [
            'profile' => $profile,
            'reports' => $this->reportService->findByUserId($userId),
            'controller_name' => 'ModeratorProfileReportsController',
        ]);
    }
}
