<?php

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Image;
use DatingLibre\AppBundle\Entity\ImageProjection;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Uid\Uuid;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function save(Image $image): Image
    {
        $this->getEntityManager()->persist($image);
        $this->getEntityManager()->flush();
        return $image;
    }

    public function delete(Image $file): void
    {
        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();
    }

    public function findProjection(Uuid $userId, bool $isProfile): ?ImageProjection
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\ImageProjection', 'ip');
        $rsm->addFieldResult('ip', 'id', 'id');
        $rsm->addFieldResult('ip', 'user_id', 'userId');
        $rsm->addFieldResult('ip', 'secure_url', 'secureUrl');
        $rsm->addFieldResult('ip', 'secure_url_expiry', 'secureUrlExpiry');
        $rsm->addFieldResult('ip', 'status', 'status');

        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT id,
        user_id, 
        secure_url,
        secure_url_expiry,
        status
        FROM datinglibre.images 
        WHERE user_id = :userId AND is_profile = :isProfile
EOD, $rsm);
        $query->setParameter('userId', $userId);
        $query->setParameter('isProfile', $isProfile);

        return $query->getOneOrNullResult();
    }

    public function findUnModerated(): ?ImageProjection
    {
        $rsm = new ResultSetMapping();

        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\ImageProjection', 'pip');
        $rsm->addFieldResult('pip', 'id', 'id');
        $rsm->addFieldResult('pip', 'user_id', 'userId');
        $rsm->addFieldResult('pip', 'secure_url', 'secureUrl');
        $rsm->addFieldResult('pip', 'secure_url_expiry', 'secureUrlExpiry');

        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT id,
        secure_url,
        secure_url_expiry,
        user_id 
        FROM datinglibre.images 
        WHERE status = :status
        LIMIT 1
EOD, $rsm);

        $query->setParameter('status', Image::UNMODERATED);
        return $query->getOneOrNullResult();
    }

    public function findByExpiredSecureUrl(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()
                ->lt('secureUrlExpiry', new DateTimeImmutable('now', new DateTimeZone('UTC'))));

        return $this->matching($criteria);
    }
}
