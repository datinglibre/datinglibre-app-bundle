<?php

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Interest;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Entity\UserInterestFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method UserInterestFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterestFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterestFilter[]    findAll()
 * @method UserInterestFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserInterestFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInterestFilter::class);
    }

    public function findInterestFiltersByUserId(Uuid $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\Interest', 'interest');
        $rsm->addFieldResult('interest', 'id', 'id');
        $rsm->addFieldResult('interest', 'name', 'name');

        $query = $this->getEntityManager()
            ->createNativeQuery(<<<EOD
SELECT i.id, i.name FROM datinglibre.user_interest_filters uif
INNER JOIN datinglibre.interests i ON i.id = uif.interest_id 
WHERE uif.user_id = :userId
EOD, $rsm);

        $query->setParameter('userId', $userId);

        return $query->getResult();
    }

    public function deleteByUserId(Uuid $userId)
    {
        $query = $this->getEntityManager()
            ->createNativeQuery(<<<EOD
DELETE FROM datinglibre.user_interest_filters uif WHERE uif.user_id = :userId 
EOD, new ResultSetMapping());

        $query->setParameter('userId', $userId);
        $query->execute();
    }

    public function save(User $user, Interest $interest): UserInterestFilter
    {
        $userInterestFilter = new UserInterestFilter();
        $userInterestFilter->setUser($user);
        $userInterestFilter->setInterest($interest);

        $this->getEntityManager()->persist($userInterestFilter);
        $this->getEntityManager()->flush();

        return $userInterestFilter;
    }
}
