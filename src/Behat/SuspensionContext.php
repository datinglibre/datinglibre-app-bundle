<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use Behat\Behat\Context\Context;
use DateInterval;
use DateTimeImmutable;
use DatingLibre\AppBundle\Repository\SuspensionRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use DatingLibre\AppBundle\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Webmozart\Assert\Assert;

class SuspensionContext implements Context
{
    private const DEFAULT_SUSPENSION_DURATION = 72;
    private SuspensionRepository $suspensionRepository;
    private SuspensionService $suspensionService;
    private UserService $userService;

    public function __construct(
        UserService $userService,
        SuspensionRepository $suspensionRepository,
        SuspensionService $suspensionService
    ) {
        $this->userService = $userService;
        $this->suspensionRepository = $suspensionRepository;
        $this->suspensionService = $suspensionService;
    }

    /**
     * @Given the moderator :moderatorEmail has suspended :userEmail for :hours hours
     */
    public function suspend(string $moderatorEmail, string $userEmail, int $hours)
    {
        $moderator = $this->userService->findByEmail($moderatorEmail);
        $user = $this->userService->findByEmail($userEmail);

        $this->suspensionService->suspend($moderator->getId(), $user->getId(), ['spam'], $hours);
    }

    /**
     * @Given the moderator :moderatorEmail suspends :userEmail again an error should be thrown
     */
    public function suspendAgain(string $moderatorEmail, string $userEmail)
    {
        $moderator = $this->userService->findByEmail($moderatorEmail);
        $user = $this->userService->findByEmail($userEmail);
        $exceptionThrown = false;

        try {
            $this->suspensionService->suspend(
                $moderator->getId(),
                $user->getId(),
                ['spam'],
                self::DEFAULT_SUSPENSION_DURATION
            );
        } catch (UniqueConstraintViolationException $exception) {
            $exceptionThrown = true;
        }

        Assert::true($exceptionThrown);
    }

    /**
     * @When :hours hours has elapsed for the suspension under :email
     */
    public function elapseSuspension(int $hours, string $email)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);
        $suspension = $this->suspensionService->findOpenByUserId($user->getId());
        Assert::notNull($suspension);
        $createdAt = $suspension->getCreatedAt();
        $dateTime = DateTimeImmutable::createFromMutable($createdAt);
        $this->suspensionRepository->setCreationTime(
            $suspension->getId(),
            $dateTime->sub(new DateInterval(sprintf('PT%dH', $hours)))
        );
    }
}
