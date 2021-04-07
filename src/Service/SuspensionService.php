<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Suspension;
use DatingLibre\AppBundle\Repository\SuspensionRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class SuspensionService
{
    private UserRepository $userRepository;
    private SuspensionRepository $suspensionRepository;

    public function __construct(
        UserRepository $userRepository,
        SuspensionRepository $suspensionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->suspensionRepository = $suspensionRepository;
    }

    public function findById(Uuid $suspensionId): ?Suspension
    {
        return $this->suspensionRepository->find($suspensionId);
    }

    public function suspend(Uuid $moderatorId, Uuid $userId, array $reasons, int $duration): Suspension
    {
        $moderator = $this->userRepository->findOneBy(['id' => $moderatorId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if ($moderator === null || $user === null) {
            throw new NotFoundHttpException();
        }

        $suspension = new Suspension();
        $suspension->setModeratorOpened($moderator);
        $suspension->setUser($user);
        $suspension->setDuration($duration);
        $suspension->setReasons($reasons);
        return $this->suspensionRepository->save($suspension);
    }

    public function findOpenByUserId(Uuid $userId): ?Suspension
    {
        return $this->suspensionRepository->findOneBy(['user' => $userId, 'status' => Suspension::OPEN]);
    }

    public function findAllByUserId(Uuid $userId): array
    {
        return $this->suspensionRepository->findAllByUserId($userId);
    }

    public function getElapsedSuspensions(): array
    {
        return $this->suspensionRepository->getElapsedSuspensions();
    }

    public function close(Uuid $moderatorId, Uuid $suspensionId): void
    {
        $suspension = $this->suspensionRepository->find($suspensionId);
        $moderator = $this->userRepository->find($moderatorId);

        if ($suspension === null) {
            return;
        }

        $suspension->setModeratorClosed($moderator);
        $suspension->setStatus(Suspension::CLOSED);
        $this->suspensionRepository->save($suspension);
    }
}
