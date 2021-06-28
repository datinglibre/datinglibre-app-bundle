<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Repository\InterestRepository;
use DatingLibre\AppBundle\Repository\UserInterestFilterRepository;
use Exception;

class UserInterestFilterService
{
    private UserInterestFilterRepository $userInterestFilterRepository;
    private InterestRepository $interestRepository;

    public function __construct(UserInterestFilterRepository $userInterestFilterRepository, InterestRepository $interestRepository)
    {
        $this->userInterestFilterRepository = $userInterestFilterRepository;
        $this->interestRepository = $interestRepository;
    }

    /**
     * @throws Exception
     */
    public function createUserInterestFilterByNames(User $user, array $interests): void
    {
        $this->userInterestFilterRepository->deleteByUserId($user->getId());

        foreach ($interests as $interest) {
            $interest = $this->interestRepository->findOneBy(['name' => $interest]);

            if ($interest !== null) {
                $this->userInterestFilterRepository->save($user, $interest);
            } else {
                throw new Exception(sprintf('Could not find interest [%s] by name', $interest));
            }
        }
    }

    public function createUserInterestFiltersByInterests(User $user, array $interests): void
    {
        $this->userInterestFilterRepository->deleteByUserId($user->getId());

        foreach ($interests as $interest) {
            $this->userInterestFilterRepository->save($user, $interest);
        }
    }

    public function findByUser(User $user): array
    {
        return $this->userInterestFilterRepository->findInterestFiltersByUserId($user->getId());
    }
}
