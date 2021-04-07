<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\MessageRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MatchesController extends AbstractController
{
    private MessageRepository $messageRepository;
    private SuspensionService $suspensionService;

    public function __construct(MessageRepository $messageRepository, SuspensionService $suspensionService)
    {
        $this->messageRepository = $messageRepository;
        $this->suspensionService = $suspensionService;
    }

    public function matches()
    {
        if ($this->suspensionService->findOpenByUserId($this->getUser()->getId()) !== null) {
            return new RedirectResponse($this->generateUrl('profile_index'));
        }

        return $this->render('@DatingLibreApp/user/matches/index.html.twig', [
            'matches' => $this->messageRepository->findLatestMessages($this->getUser()->getId()),
            'controller_name' => 'MatchesController'
        ]);
    }
}
