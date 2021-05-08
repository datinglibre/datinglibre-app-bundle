<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AdminSuspensionQueueController extends AbstractController
{
    private SuspensionService $suspensionService;

    public function __construct(SuspensionService $suspensionService)
    {
        $this->suspensionService = $suspensionService;
    }

    public function view(): Response
    {
        return $this->render('@DatingLibreApp/admin/suspensions/index.html.twig', [
            'suspensions' => $this->suspensionService->findOpenPermanentSuspensions()
        ]);
    }
}
