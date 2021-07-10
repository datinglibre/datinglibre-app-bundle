<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Block;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Block|null find($id, $lockMode = null, $lockVersion = null)
 * @method Block|null findOneBy(array $criteria, array $orderBy = null)
 * @method Block[]    findAll()
 * @method Block[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Block::class);
    }

    public function findProfileProjectionsByUserId(Uuid $userId): array
    {
        $sql = <<<EOD
SELECT p.user_id,
    u.last_login,   
    EXTRACT(YEAR FROM AGE(p.dob)) as age,
    p.username,
    p.about,
    p.meta,
    p.status as profile_status,   
    profileImage.secure_url,
    profileImage.status as image_status, 
    city.name as city_name,
    region.name as region_name,
    p.status
    FROM datinglibre.blocks b 
    INNER JOIN datinglibre.profiles p ON b.blocked_user_id = p.user_id
    INNER JOIN datinglibre.users u ON u.id = p.user_id    
    INNER JOIN datinglibre.cities city ON p.city_id = city.id
    INNER JOIN datinglibre.regions region ON region.id = city.region_id 
    LEFT JOIN datinglibre.images profileImage ON profileImage.user_id = p.user_id AND profileImage.is_profile = TRUE AND profileImage.status = 'ACCEPTED'
    WHERE b.user_id = :userId
EOD;
        $query = $this->getEntityManager()->createNativeQuery($sql, $this->getResultSetMapper());

        $query->setParameter('userId', $userId);
        return $query->getResult();
    }

    public function save(Block $block): Block
    {
        $this->getEntityManager()->persist($block);
        $this->getEntityManager()->flush();

        return $block;
    }


    public function getResultSetMapper(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();

        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\ProfileProjection', 'pv');
        $rsm->addFieldResult('pv', 'user_id', 'id');
        $rsm->addFieldResult('pv', 'username', 'username');
        $rsm->addFieldResult('pv', 'age', 'age');
        $rsm->addFieldResult('pv', 'city_name', 'cityName');
        $rsm->addFieldResult('pv', 'region_name', 'regionName');
        $rsm->addFieldResult('pv', 'last_login', 'lastLogin');
        $rsm->addFieldResult('pv', 'about', 'about');
        $rsm->addFieldResult('pv', 'sort_id', 'sortId');
        $rsm->addFieldResult('pv', 'secure_url', 'imageUrl');
        $rsm->addFieldResult('pv', 'profile_status', 'profileStatus');
        $rsm->addFieldResult('pv', 'image_status', 'imageStatus');

        return $rsm;
    }

    public function deleteByUserIdAndBlockedUserId(Uuid $userId, Uuid $blockedUserId): void
    {
        $block = $this->findOneBy(['user' => $userId, 'blockedUser' => $blockedUserId]);

        if ($block === null) {
            return;
        }

        $this->getEntityManager()->remove($block);
        $this->getEntityManager()->flush();
    }
}
