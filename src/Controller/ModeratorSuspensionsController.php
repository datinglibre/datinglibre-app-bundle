<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ModeratorSuspensionsController extends AbstractController
{
    private SuspensionService $suspensionService;

    public function __construct(SuspensionService $suspensionService)
    {
        $this->suspensionService = $suspensionService;
    }

    public function index(): Response
    {

        $elapsedSuspensions = $this->suspensionService->getElapsedSuspensions();
        return $this->render('@DatingLibreApp/moderator/suspension/index.html.twig', [
            'suspensions' => $elapsedSuspensions
        ]);
    }
}
