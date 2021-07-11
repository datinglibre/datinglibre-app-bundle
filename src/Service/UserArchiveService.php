<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\UserArchive;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Repository\UserArchiveRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;

class UserArchiveService
{
    private UserArchiveRepository $userArchiveRepository;
    private ProfileRepository $profileRepository;
    private UserRepository $userRepository;

    public function __construct(
        UserRepository $userRepository,
        ProfileRepository $profileRepository,
        UserArchiveRepository $userArchiveRepository
    ) {
        $this->userRepository = $userRepository;
        $this->profileRepository = $profileRepository;
        $this->userArchiveRepository = $userArchiveRepository;
    }

    public function saveArchive(Uuid $userId): UserArchive
    {
        $archive = [];
        $profile = $this->profileRepository->find($userId);
        $user = $this->userRepository->find($userId);

        if ($profile !== null) {
            $archive['profile']['username'] = $profile->getUsername();
        }

        $userArchive = new UserArchive();
        $userArchive->setEmail($user->getEmail());
        $userArchive->setArchive($archive);
        return $this->userArchiveRepository->save($userArchive);
    }

    public function findByEmail(string $email): ?UserArchive
    {
        return $this->userArchiveRepository->findOneBy(['email' => $email]);
    }

    public function deleteOlderThanDays(int $days): void
    {
        $this->userArchiveRepository->deleteOlderThanDays($days);
    }
}
