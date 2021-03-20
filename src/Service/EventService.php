<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Event;
use DatingLibre\AppBundle\Repository\EventRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;

class EventService
{
    private EventRepository $eventRepository;
    private UserRepository $userRepository;

    public function __construct(EventRepository $eventRepository, UserRepository $userRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
    }

    public function save(?Uuid $userId, string $eventName, array $data): Event
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        $event = new Event();

        if ($user !== null) {
            $event->setUser($user);
        }

        $event->setName($eventName);
        $event->setData($data);
        return $this->eventRepository->save($event);
    }

    public function findAll(): array
    {
        return $this->eventRepository->findAll();
    }
}
