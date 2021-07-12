<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Email;
use DatingLibre\AppBundle\Entity\Profile;
use DatingLibre\AppBundle\Entity\Suspension;
use DatingLibre\AppBundle\Entity\SuspensionProjection;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Repository\SuspensionRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Message;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuspensionService
{
    private LoggerInterface $logger;
    private UserRepository $userRepository;
    private ProfileRepository $profileRepository;
    private SuspensionRepository $suspensionRepository;
    private EmailService $emailService;
    private TranslatorInterface $translator;
    private string $adminEmail;
    private EntityManager $entityManager;
    private SubscriptionService $subscriptionService;

    public function __construct(
        LoggerInterface $logger,
        EntityManager $entityManager,
        UserRepository $userRepository,
        ProfileRepository $profileRepository,
        SuspensionRepository $suspensionRepository,
        EmailService $emailService,
        SubscriptionService $subscriptionService,
        TranslatorInterface $translator,
        string $adminEmail
    ) {
        $this->userRepository = $userRepository;
        $this->suspensionRepository = $suspensionRepository;
        $this->profileRepository = $profileRepository;
        $this->emailService = $emailService;
        $this->subscriptionService = $subscriptionService;
        $this->adminEmail = $adminEmail;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function findById(Uuid $suspensionId): ?Suspension
    {
        return $this->suspensionRepository->find($suspensionId);
    }

    public function enqueuePermanentSuspension(Uuid $moderatorId, Uuid $userId, array $reasons): Suspension
    {
        return $this->suspend($moderatorId, $userId, $reasons, null);
    }

    /**
     * @throws Exception
     */
    public function suspend(Uuid $moderatorId, Uuid $userId, array $reasons, ?int $duration): Suspension
    {
        $moderator = $this->userRepository->findOneBy(['id' => $moderatorId]);
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if ($moderator === null || $user === null) {
            throw new NotFoundHttpException();
        }

        $profile = $this->profileRepository->find($user->getId());

        $this->entityManager->beginTransaction();

        try {
            $this->suspensionRepository->closeAllByUserId($user->getId());
            $suspension = new Suspension();
            $suspension->setUserOpened($moderator);
            $suspension->setUser($user);
            $suspension->setDuration($duration);
            $suspension->setReasons($reasons);
            $suspension = $this->suspensionRepository->save($suspension);

            $profile->setStatus(Profile::SUSPENDED);
            $this->profileRepository->save($profile);

            $email = (new TemplatedEmail())
                ->from($this->adminEmail)
                ->context(['reasons' => $reasons, 'hours' => $duration])
                ->subject($this->translator->trans('suspension.suspended_subject'))
                ->to($user->getEmail())
                ->htmlTemplate('@DatingLibreApp/admin/suspension/email/suspended.html.twig');

            $this->emailService->send($email, $user, Email::SUSPENSION);
            $this->entityManager->commit();
            return $suspension;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    public function findOpenByUserId(Uuid $userId): ?Suspension
    {
        return $this->suspensionRepository->findOneBy(
            [
                'user' => $userId,
                'status' => Suspension::OPEN
            ]
        );
    }

    public function findElapsedAndOpenByUserId(Uuid $userId): ?SuspensionProjection
    {
        return $this->suspensionRepository->findElapsedAndOpenByUserId($userId);
    }

    public function findAllByUserId(Uuid $userId): array
    {
        return $this->suspensionRepository->findAllByUserId($userId);
    }

    public function findElapsedSuspensions(): array
    {
        return $this->suspensionRepository->findElapsedSuspensions();
    }

    /**
     * @throws Exception
     */
    public function close(Uuid $moderatorId, Uuid $suspensionId): void
    {
        $suspension = $this->suspensionRepository->find($suspensionId);
        $moderator = $this->userRepository->find($moderatorId);

        if ($suspension === null) {
            return;
        }

        $this->entityManager->beginTransaction();

        try {
            $profile = $this->profileRepository->find($suspension->getUser()->getId());
            $profile->setStatus(Profile::ACCEPTED);
            $this->profileRepository->save($profile);

            $suspension->setUserClosed($moderator);
            $suspension->setStatus(Suspension::CLOSED);
            $this->suspensionRepository->save($suspension);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function findOpenPermanentSuspension(Uuid $userId): ?Suspension
    {
        return $this->suspensionRepository->findOneBy(
            [
                'user' => $userId,
                'status' => Suspension::OPEN,
                'duration' => null
            ]
        );
    }

    public function findOpenPermanentSuspensions(): array
    {
        return $this->suspensionRepository->findOpenPermanentSuspensions();
    }

    /**
     * @throws Exception
     */
    public function permanentlySuspend(Uuid $userId, Uuid $suspendedUserId, array $reasons): void
    {

        $user = $this->userRepository->find($userId);
        $suspendedUser = $this->userRepository->find($suspendedUserId);
        $suspendedProfile = $this->profileRepository->find($suspendedUserId);

        $this->subscriptionService->cancel($suspendedUser->getId());

        $this->entityManager->beginTransaction();
        try {
            $this->suspensionRepository->closeAllByUserId($suspendedUserId);
            $permanentSuspension = new Suspension();
            $permanentSuspension->setUser($suspendedUser);
            $permanentSuspension->setUserOpened($user);
            $permanentSuspension->setReasons($reasons);
            $this->suspensionRepository->save($permanentSuspension);

            $suspendedProfile->setStatus(Profile::PERMANENTLY_SUSPENDED);
            $this->profileRepository->save($suspendedProfile);

            $email = (new TemplatedEmail())
                ->from($this->adminEmail)
                ->context(['reasons' => $reasons])
                ->subject($this->translator->trans('suspension.permanently_suspended_subject'))
                ->to($suspendedUser->getEmail())
                ->htmlTemplate('@DatingLibreApp/admin/suspension/email/permanently_suspended.html.twig');

            $this->emailService->send($email, $suspendedUser, Email::PERMANENT_SUSPENSION);
            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->entityManager->rollback();
            throw $exception;
        }
    }
}
