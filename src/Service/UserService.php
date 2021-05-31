<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Email;
use DatingLibre\AppBundle\Entity\Token;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService
{
    private EntityManager $entityManager;
    private UserRepository $userRepository;
    private EmailService $emailService;
    private TokenService $tokenService;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ProfileService $profileService;
    private TranslatorInterface $translator;
    private string $adminEmail;

    public function __construct(
        EntityManager $entityManager,
        UserRepository $userRepository,
        EmailService $emailService,
        TokenService $tokenService,
        ProfileService $profileService,
        UserPasswordEncoderInterface $passwordEncoder,
        TranslatorInterface $translator,
        string $adminEmail
    ) {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->profileService = $profileService;
        $this->emailService = $emailService;
        $this->tokenService = $tokenService;
        $this->adminEmail = $adminEmail;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function create(
        string $email,
        string $password,
        bool $enabled,
        array $roles
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $user->setRoles($roles);
        $user->setEnabled($enabled);

        return $this->userRepository->save($user);
    }

    public function delete(?Uuid $deletedById, Uuid $userId)
    {
        $this->profileService->delete($userId);
        $this->userRepository->delete($userId);
    }

    public function deleteByPassword(?Uuid $userId, string $password): bool
    {
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            return false;
        }

        if ($this->passwordEncoder->isPasswordValid($user, $password)) {
            $this->delete(null, $user->getId());
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public function signup(string $email, string $password): void
    {
        $existingUser = $this->findByEmail($email);

        if (null !== $existingUser) {
            $email = (new TemplatedEmail())
                ->from($this->adminEmail)
                ->subject($this->translator->trans('registration.already_exists_subject'))
                ->to($existingUser->getEmail())
                ->htmlTemplate('@DatingLibreApp/user/account/email/already_exists.html.twig');

            $this->emailService->send($email, $existingUser, Email::ALREADY_EXISTS);
            return;
        }

        $this->entityManager->beginTransaction();
        try {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setRoles([User::USER]);
            $savedUser = $this->userRepository->save($user);
            $token = $this->tokenService->save($savedUser, Token::SIGNUP);

            $email = (new TemplatedEmail())
                ->from($this->adminEmail)
                ->subject($this->translator->trans('user.signup_subject'))
                ->to($savedUser->getEmail())
                ->htmlTemplate('@DatingLibreApp/user/account/email/confirm.html.twig')
                ->context(['secret' => $token->getSecret(), 'userId' => $savedUser->getId()->toRfc4122()]);

            $this->emailService->send($email, $savedUser, Email::SIGNUP);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function updatePassword(string $userId, string $secret, string $newPassword): bool
    {
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            return false;
        }

        if ($this->tokenService->verify($user, $secret, Token::PASSWORD_RESET)) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $newPassword));
            $this->userRepository->save($user);
            return true;
        }

        return false;
    }

    public function resetPassword(string $email): void
    {
        $user = $this->userRepository->findOneBy([User::EMAIL => $email]);

        if ($user == null) {
            return;
        }

        $token = $this->tokenService->save($user, Token::PASSWORD_RESET);

        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->subject($this->translator->trans('user.reset_password_subject'))
            ->to($user->getEmail())
            ->htmlTemplate('@DatingLibreApp/user/account/email/password_reset.html.twig')
            ->context(['secret' => $token->getSecret(), 'userId' => $user->getId()->toRfc4122()]);

        $this->emailService->send($email, $user, Email::PASSWORD_RESET);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy([User::EMAIL => $email]);
    }

    public function enable(string $userId, $secret): bool
    {
        if (empty($userId)) {
            return false;
        }

        $user = $this->userRepository->find($userId) ;

        if ($user == null) {
            return false;
        }

        if ($this->tokenService->verify($user, $secret, Token::SIGNUP)) {
            $user->setEnabled(true);
            $this->userRepository->save($user);
            return true;
        }

        return false;
    }

    public function purge(string $type, int $hours): void
    {
        $users = [];

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $interval = new DateInterval(sprintf('PT%dH', $hours));

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()
                ->lt('createdAt', $now->sub($interval)));

        switch ($type) {
            case 'ALL':
                $users = $this->userRepository->matching($criteria);
                break;
            case 'NOT_ENABLED':
                $criteria->andWhere(Criteria::expr()->eq(User::ENABLED, false));
                $users = $this->userRepository->matching($criteria);
                break;
        }

        /** @var User $user */
        foreach ($users as $user) {
            $roles = $user->getRoles();

            if (in_array(User::ADMIN, $roles)
                || in_array(User::MODERATOR, $roles)) {
                continue;
            }

            $this->delete(null, $user->getId());
        }
    }
}
