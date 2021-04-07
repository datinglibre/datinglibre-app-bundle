<?php

namespace DatingLibre\AppBundle\Repository;

use DateTimeInterface;
use DatingLibre\AppBundle\Entity\Suspension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Suspension|null find($id, $lockMode = null, $lockVersion = null)
 * @method Suspension|null findOneBy(array $criteria, array $orderBy = null)
 * @method Suspension[]    findAll()
 * @method Suspension[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuspensionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suspension::class);
    }

    public function save(Suspension $suspension): Suspension
    {
        $this->getEntityManager()->persist($suspension);
        $this->getEntityManager()->flush();

        return $suspension;
    }

    public function setCreationTime(Uuid $suspensionId, DateTimeInterface $creationTime): void
    {
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
UPDATE datinglibre.suspensions SET created_at = :createdAt WHERE id = :suspensionId
EOD, new ResultSetMapping());

        $query->setParameter('suspensionId', $suspensionId);
        $query->setParameter('createdAt', $creationTime);

        $query->execute();
    }

    public function findAllByUserId(Uuid $userId)
    {
        $sql =<<<EOD
SELECT 
    s.id AS id,
    s.user_id AS user_id,
    s.reasons AS reasons,   
    s.duration AS duration,
    s.created_at AS created_at,
    p.username AS username,
    s.created_at + interval '1  hour' * s.duration < now() AS elapsed,
    s.status AS status   
FROM datinglibre.suspensions s
    INNER JOIN datinglibre.profiles p ON p.user_id = s.user_id
WHERE s.user_id = :userId;
EOD;
        $query = $this->getEntityManager()->createNativeQuery($sql, $this->getSuspensionProjectionMapping());
        $query->setParameter('userId', $userId);
        return $query->getResult();
    }

    public function getElapsedSuspensions(): array
    {
        $sql =<<<EOD
SELECT 
    s.id AS id,
    s.user_id AS user_id,
    s.reasons AS reasons,   
    s.duration AS duration,
    s.created_at AS created_at,
    p.username AS username,
    s.created_at + interval '1  hour' * s.duration < now() AS elapsed,
    s.status AS status
FROM datinglibre.suspensions s
    INNER JOIN datinglibre.profiles p ON p.user_id = s.user_id 
WHERE s.created_at + interval '1  hour' * s.duration < now();
EOD;
        $query = $this->getEntityManager()->createNativeQuery($sql, $this->getSuspensionProjectionMapping());
        return $query->getResult();
    }

    public function getSuspensionProjectionMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\SuspensionProjection', 'sp');
        $rsm->addFieldResult('sp', 'id', 'id');
        $rsm->addFieldResult('sp', 'user_id', 'userId');
        $rsm->addFieldResult('sp', 'reasons', 'reasons');
        $rsm->addFieldResult('sp', 'duration', 'duration');
        $rsm->addFieldResult('sp', 'created_at', 'createdAt');
        $rsm->addFieldResult('sp', 'status', 'status');
        $rsm->addFieldResult('sp', 'username', 'username');
        $rsm->addFieldResult('sp', 'elapsed', 'elapsed');
        return $rsm;
    }
}
