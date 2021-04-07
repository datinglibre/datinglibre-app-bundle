<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function save(Report $report): Report
    {
        $this->getEntityManager()->persist($report);
        $this->getEntityManager()->flush();

        return $report;
    }

    public function findByStatus(string $reportStatus): array
    {
        $sql = <<<EOD
SELECT r.id as id,
       r.user_id AS reporter_id,
       r.created_at AS created_at,
       r.updated_at AS updated_at,
       r.reasons AS reasons,
       r.status AS status,
       r.reported_user_id AS reported_id,
       reporter_profile.username AS reporter_username,
       reported_profile.username AS reported_username
FROM datinglibre.reports r
LEFT JOIN datinglibre.profiles reporter_profile ON reporter_profile.user_id = r.user_id 
INNER JOIN datinglibre.profiles reported_profile ON reported_profile.user_id = r.reported_user_id
WHERE r.status = :status
EOD;
        $query = $this->getEntityManager()->createNativeQuery($sql, $this->getReportProjectionMapping());
        $query->setParameter('status', $reportStatus);
        return $query->getResult();
    }

    public function findById(Uuid $reportId)
    {
        $sql = <<<EOD
SELECT r.id as id,
       r.user_id AS reporter_id,
       r.created_at AS created_at,
       r.updated_at AS updated_at,
       r.reasons AS reasons,
       r.status AS status,
       r.reported_user_id AS reported_id,
       reporter_profile.username AS reporter_username,
       reported_profile.username AS reported_username
FROM datinglibre.reports r
LEFT JOIN datinglibre.profiles reporter_profile ON reporter_profile.user_id = r.user_id 
INNER JOIN datinglibre.profiles reported_profile ON reported_profile.user_id = r.reported_user_id
WHERE r.id = :reportId
EOD;
        $query = $this->getEntityManager()->createNativeQuery($sql, $this->getReportProjectionMapping());
        $query->setParameter('reportId', $reportId);
        return $query->getOneOrNullResult();
    }

    public function findByUserId(Uuid $userId): array
    {
        $sql = <<<EOD
SELECT r.id as id,
       r.user_id AS reporter_id,
       r.created_at AS created_at,
       r.updated_at AS updated_at,
       r.reasons AS reasons,
       r.status AS status,
       r.reported_user_id AS reported_id,
       reporter_profile.username AS reporter_username,
       reported_profile.username AS reported_username
FROM datinglibre.reports r
LEFT JOIN datinglibre.profiles reporter_profile ON reporter_profile.user_id = r.user_id 
INNER JOIN datinglibre.profiles reported_profile ON reported_profile.user_id = r.reported_user_id
WHERE r.reported_user_id = :userId
EOD;
        $query = $this->getEntityManager()->createNativeQuery($sql, $this->getReportProjectionMapping());
        $query->setParameter('userId', $userId);
        return $query->getResult();
    }

    public function getReportProjectionMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\ReportProjection', 'rp');
        $rsm->addFieldResult('rp', 'id', 'id');
        $rsm->addFieldResult('rp', 'reporter_id', 'reporterId');
        $rsm->addFieldResult('rp', 'reporter_username', 'reporterUsername');
        $rsm->addFieldResult('rp', 'reported_id', 'reportedId');
        $rsm->addFieldResult('rp', 'reported_username', 'reportedUsername');
        $rsm->addFieldResult('rp', 'reasons', 'reasons');
        $rsm->addFieldResult('rp', 'status', 'status');
        $rsm->addFieldResult('rp', 'created_at', 'createdAt');
        $rsm->addFieldResult('rp', 'updated_at', 'updatedAt');

        return $rsm;
    }
}
