<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\CloseReportForm;
use DatingLibre\AppBundle\Form\CloseReportFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\MessageService;
use DatingLibre\AppBundle\Service\ReportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorProfileReportController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private ReportService $reportService;
    private MessageService $messageService;

    public function __construct(
        ProfileRepository $profileRepository,
        ReportService $reportService,
        MessageService $messageService
    ) {
        $this->profileRepository = $profileRepository;
        $this->reportService = $reportService;
        $this->messageService = $messageService;
    }

    public function view(Request $request, Uuid $userId, Uuid $reportId)
    {
        $profile = $this->profileRepository->findProjection($userId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        $report = $this->reportService->findProjectionById($reportId);

        if ($report === null) {
            throw $this->createNotFoundException();
        }

        $closeReportForm = new CloseReportForm();
        $closeReportForm->setReport($this->reportService->findById($reportId));

        $closeReportFormType = $this->createForm(CloseReportFormType::class, $closeReportForm);
        $closeReportFormType->handleRequest($request);

        if ($closeReportFormType->isSubmitted() && $closeReportFormType->isValid()) {
            $this->reportService->close($this->getUser()->getId(), $report->getId());
            $this->addFlash('success', 'report.closed_confirmation');
            return $this->redirectToRoute('moderator_profile_reports', ['userId' => $report->getReportedId()]);
        }

        return $this->render('@DatingLibreApp/moderator/profile/report.html.twig', [
            'profile' => $profile,
            'messages' => $this->messageService->findMessagesBetweenUsers($report->getReportedId(), $report->getReporterId()),
            'reports' => [$report],
            'closeReportForm' => $closeReportFormType->createView(),
            'controller_name' => 'ModeratorProfileReportsController',
        ]);
    }
}
