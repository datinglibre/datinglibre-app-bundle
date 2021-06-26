<?php

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Interest;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Entity\UserInterest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method UserInterest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterest[]    findAll()
 * @method UserInterest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserInterestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInterest::class);
    }

    public function findInterestsByUserId(Uuid $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\Interest', 'interest');
        $rsm->addFieldResult('interest', 'id', 'id');
        $rsm->addFieldResult('interest', 'name', 'name');

        $query = $this->getEntityManager()
            ->createNativeQuery(<<<EOD
SELECT i.id, i.name FROM datinglibre.user_interests ui 
INNER JOIN datinglibre.interests i ON i.id = ui.interest_id 
WHERE ui.user_id = :userId
EOD, $rsm);

        $query->setParameter('userId', $userId);

        return $query->getResult();
    }

    public function save(User $user, Interest $interest): UserInterest
    {
        $userInterest = new UserInterest();
        $userInterest->setUser($user);
        $userInterest->setInterest($interest);

        $this->getEntityManager()->persist($userInterest);
        $this->getEntityManager()->flush();

        return $userInterest;
    }

    public function deleteByUserId(Uuid $userId)
    {
        $query = $this->getEntityManager()
            ->createNativeQuery(<<<EOD
DELETE FROM datinglibre.user_interests ui WHERE ui.user_id = :userId 
EOD, new ResultSetMapping());

        $query->setParameter('userId', $userId);
        $query->execute();
    }
}
