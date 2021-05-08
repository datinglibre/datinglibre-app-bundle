<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModeratorReportsController extends AbstractController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request): Response
    {
        return $this->render('@DatingLibreApp/moderator/reports/index.html.twig', [
            'controller_name' => 'ModerateReportsController',
            'reports' => $this->reportService->findCreated()
        ]);
    }
}
