<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\ReportForm;
use DatingLibre\AppBundle\Form\ReportFormType;
use DatingLibre\AppBundle\Service\ProfileService;
use DatingLibre\AppBundle\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ReportController extends AbstractController
{
    private ReportService $reportService;
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService, ReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->profileService = $profileService;
    }

    public function report(Uuid $userId, Request $request)
    {
        $profile = $this->profileService->findProjectionByCurrentUser($this->getUser()->getId(), $userId);

        if (null === $profile) {
            throw $this->createNotFoundException();
        }

        $report = $this->reportService->find($this->getUser()->getId(), $userId);

        if ($report !== null) {
            $this->addFlash('danger', 'report.reported');
            return $this->redirectToRoute('user_profile_view', ['userId' => $userId]);
        }

        $reportForm = new ReportForm();
        $reportFormType = $this->createForm(ReportFormType::class, $reportForm);
        $reportFormType->handleRequest($request);

        if ($reportFormType->isSubmitted() && $reportFormType->isValid()) {
            $this->addFlash('success', 'report.success');

            $this->reportService->report($this->getUser()->getId(), $userId, $reportForm->getMessage(), $reportForm->getReasons());
            return $this->redirectToRoute('user_profile_view', ['userId' => $userId]);
        }

        return $this->render(
            '@DatingLibreApp/user/report/create.html.twig',
            [
                'controller_name' => 'ReportController',
                'reportForm' => $reportFormType->createView(),
                'profile' => $profile
            ]
        );
    }
}
