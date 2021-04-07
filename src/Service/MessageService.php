<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Repository\MessageRepository;
use Symfony\Component\Uid\Uuid;

class MessageService
{
    private MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function findMessagesBetweenUsers(?Uuid $firstUserId, ?Uuid $secondUserId): array
    {
        if ($firstUserId === null || $secondUserId === null) {
            return [];
        }
        return $this->messageRepository->findMessagesBetweenUsers($firstUserId, $secondUserId);
    }
}
