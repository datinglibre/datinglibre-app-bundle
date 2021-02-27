<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MatchesController extends AbstractController
{
    private MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function matches()
    {
        return $this->render('@DatingLibreApp/matches/index.html.twig', [
            'matches' => $this->messageRepository->findLatestMessages($this->getUser()->getId()),
            'controller_name' => 'MatchesController'
        ]);
    }
}
