<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Repository\InterestRepository;
use DatingLibre\AppBundle\Repository\UserInterestRepository;
use Exception;
use Symfony\Component\Uid\Uuid;

class UserInterestService
{
    private InterestRepository $interestRepository;
    private UserInterestRepository $userInterestRepository;

    public function __construct(InterestRepository $interestRepository, UserInterestRepository $userInterestRepository)
    {
        $this->interestRepository = $interestRepository;
        $this->userInterestRepository = $userInterestRepository;
    }

    /**
     * @throws Exception
     */
    public function createUserInterestsByNames(User $user, array $interests)
    {
        $this->userInterestRepository->deleteByUserId($user->getId());

        foreach ($interests as $interestName) {
            $interest = $this->interestRepository->findOneBy(['name' => $interestName]);

            if ($interest !== null) {
                $this->userInterestRepository->save($user, $interest);
            } else {
                throw new Exception(sprintf('Unrecognized interest name [%s]', $interestName));
            }
        }
    }

    public function createUserInterestsByInterests(User $user, array $interests)
    {
        $this->userInterestRepository->deleteByUserId($user->getId());

        foreach ($interests as $interest) {
            $this->userInterestRepository->save($user, $interest);
        }
    }

    public function findInterestsByUserId(Uuid $userId): array
    {
        return $this->userInterestRepository->findInterestsByUserId($userId);
    }
}
