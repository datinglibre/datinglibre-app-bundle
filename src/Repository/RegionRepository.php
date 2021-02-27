<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Region|null find($id, $lockMode = null, $lockVersion = null)
 * @method Region|null findOneBy(array $criteria, array $orderBy = null)
 * @method Region[]    findAll()
 * @method Region[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function findByCountry(Uuid $countryId): array
    {
        $rsm = new ResultSetMapping();

        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\LocationProjection', 'l');
        $rsm->addFieldResult('l', 'id', 'id');
        $rsm->addFieldResult('l', 'name', 'name');

        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT id, name FROM datinglibre.regions WHERE country_id = :countryId ORDER BY name ASC
EOD, $rsm);

        $query->setParameter('countryId', $countryId);

        return $query->getResult();
    }
}
