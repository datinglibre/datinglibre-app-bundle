<?php

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findByRegion(Uuid $regionId)
    {
        $rsm = new ResultSetMapping();

        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\LocationProjection', 'l');
        $rsm->addFieldResult('l', 'id', 'id');
        $rsm->addFieldResult('l', 'name', 'name');

        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT id, name FROM datinglibre.cities WHERE region_id = :regionId ORDER BY name ASC
EOD, $rsm);

        $query->setParameter('regionId', $regionId);

        return $query->getResult();
    }
}
