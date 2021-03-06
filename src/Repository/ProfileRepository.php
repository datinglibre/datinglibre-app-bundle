<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Profile;
use DatingLibre\AppBundle\Entity\ProfileProjection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Uid\Uuid;

/**
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profile[]    findAll()
 * @method Profile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    private const MAX_AGE = 100;
    private const MIN_AGE = 18;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function findByLocation(
        Uuid $userId,
        float $latitude,
        float $longitude,
        ?int $distance,
        ?Uuid $regionId,
        int $minAge,
        int $maxAge,
        int $previous,
        int $next,
        int $limit
    ): array {
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
    p.status, 
    p.sort_id as sort_id
FROM datinglibre.profiles AS p
INNER JOIN datinglibre.cities AS city ON p.city_id = city.id 
INNER JOIN datinglibre.regions AS region ON city.region_id = region.id
INNER JOIN datinglibre.users AS u ON u.id = p.user_id
LEFT JOIN datinglibre.images profileImage ON profileImage.user_id = p.user_id AND profileImage.is_profile = TRUE AND profileImage.status = 'ACCEPTED'
LEFT JOIN datinglibre.filters filter ON filter.user_id = p.user_id
WHERE p.user_id <> :userId
AND (p.status = 'UNMODERATED' OR p.status = 'ACCEPTED') 
AND EXISTS (SELECT matching_user_id FROM (
        SELECT ua.user_id AS matching_user_id FROM datinglibre.requirements r 
        LEFT JOIN datinglibre.user_attributes ua ON ua.attribute_id = r.attribute_id 
        LEFT JOIN datinglibre.attributes a ON r.attribute_id = a.id
        WHERE r.user_id = :userId 
        AND ua.user_id = p.user_id
        GROUP BY ua.user_id
        HAVING COUNT(DISTINCT a.category_id) = (SELECT COUNT(id) from datinglibre.categories)
    ) AS matches
    LEFT JOIN datinglibre.requirements match_r ON match_r.user_id = matching_user_id 
    LEFT JOIN datinglibre.user_attributes match_c ON match_c.attribute_id = match_r.attribute_id 
    AND match_c.user_id = :userId 
    LEFT JOIN datinglibre.attributes match_a ON match_a.id = match_c.attribute_id 
    GROUP BY matching_user_id
    HAVING COUNT(DISTINCT match_a.category_id) = (SELECT COUNT(id) FROM datinglibre.categories)
)
AND NOT EXISTS (
    SELECT 1 FROM datinglibre.blocks b 
        WHERE (b.user_id = :userId AND b.blocked_user_id = p.user_id) 
        OR (b.user_id = p.user_id AND b.blocked_user_id = :userId)
)
AND (SELECT EXTRACT(YEAR FROM AGE(dob)) FROM datinglibre.profiles p WHERE p.user_id = :userId)
     BETWEEN COALESCE(filter.min_age, :systemMinAge) AND COALESCE(filter.max_age, :systemMaxAge)
     AND EXTRACT(YEAR FROM AGE(p.dob)) BETWEEN :minAge AND :maxAge
AND CASE WHEN (SELECT COUNT(1) FROM datinglibre.user_interest_filters uif WHERE uif.user_id = :userId) > 0 
        THEN (SELECT COUNT(1) FROM datinglibre.user_interest_filters uif 
              INNER JOIN datinglibre.user_interests ui ON uif.interest_id = ui.interest_id 
              WHERE uif.user_id = :userId AND ui.user_id = p.user_id) > 0 
        ELSE TRUE
    END 
EOD;

        $radiusSql = 'ST_DWithin(Geography(ST_MakePoint(city.longitude, city.latitude)), Geography(ST_MakePoint(:longitude, :latitude)), :radius, false)';
        $regionSql = 'region.id = :regionId ';

        if ($distance !== null && $regionId === null) {
            $sql .= 'AND ' . $radiusSql;
        }

        if ($regionId !== null && $distance == null) {
            $sql .= 'AND ' . $regionSql;
        }

        if ($regionId !== null &&  $distance !== null) {
            $sql .= 'AND (' . $radiusSql . 'OR ' . $regionSql . ') ';
        }

        if ($previous === 0 && $next === 0) {
            $sql .= 'ORDER BY p.sort_id ASC';
        }

        if ($next !== 0) {
            $sql .= 'AND p.sort_id >= :next ORDER BY p.sort_id ASC';
        }

        if ($previous !== 0) {
            $sql .= 'AND p.sort_id <= :previous ORDER BY p.sort_id DESC';
        }

        $sql .= ' LIMIT :limit';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $userId);
        $query->setParameter('latitude', $latitude);
        $query->setParameter('longitude', $longitude);
        $query->setParameter('radius', $distance);
        $query->setParameter('regionId', $regionId);
        $query->setParameter('minAge', $minAge);
        $query->setParameter('maxAge', $maxAge);
        $query->setParameter('systemMaxAge', self::MAX_AGE);
        $query->setParameter('systemMinAge', self::MIN_AGE);


        if ($previous !== 0) {
            $query->setParameter('previous', $previous);
        }

        if ($next !== 0) {
            $query->setParameter('next', $next);
        }

        $query->setParameter('limit', $limit + 1);

        $profiles = $query->getResult();

        // sort here, as query is already complicated
        usort($profiles, fn ($a, $b) => ($a->getSortId()) > $b->getSortId());
        return $profiles;
    }

    public function save(Profile $profile): Profile
    {
        $this->getEntityManager()->persist($profile);
        $this->getEntityManager()->flush();

        return $profile;
    }

    public function findProjection(Uuid $userId): ?ProfileProjection
    {
        $rsm = $this->getProjectionResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT p.user_id,
           EXTRACT(YEAR FROM AGE(p.dob)) as age,
           p.username,
           p.about,
           p.meta,
           image.secure_url,
           city.name as city_name,
           region.name as region_name,
           p.status as profile_status,
           image.status as image_status,
           u.last_login as last_login
           FROM datinglibre.profiles p 
           INNER JOIN datinglibre.users u ON u.id = p.user_id
           LEFT JOIN datinglibre.images image ON image.user_id = p.user_id AND image.is_profile IS TRUE 
           LEFT JOIN datinglibre.cities city ON city.id = p.city_id
           LEFT JOIN datinglibre.regions region ON region.id = city.region_id 
           WHERE p.user_id = :userId
EOD, $rsm);

        $query->setParameter('userId', $userId);
        return $query->getOneOrNullResult();
    }

    public function findProjectionByEmail(string $email): ?ProfileProjection
    {
        $rsm = $this->getProjectionResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT p.user_id,
           EXTRACT(YEAR FROM AGE(p.dob)) as age,
           p.username,
           p.about,
           p.meta,
           image.secure_url,
           city.name as city_name,
           region.name as region_name,
           p.status as profile_status,
           image.status as image_status,
           u.last_login as last_login
           FROM datinglibre.profiles p 
           INNER JOIN datinglibre.users u ON u.id = p.user_id
           LEFT JOIN datinglibre.images image ON image.user_id = p.user_id AND image.is_profile IS TRUE 
           LEFT JOIN datinglibre.cities city ON city.id = p.city_id
           LEFT JOIN datinglibre.regions region ON region.id = city.region_id 
           WHERE u.email = :email
EOD, $rsm);

        $query->setParameter('email', $email);
        return $query->getOneOrNullResult();
    }

    public function findProjectionByCurrentUser(Uuid $currentUserId, Uuid $userId): ?ProfileProjection
    {
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
            SELECT p.user_id AS user_id,
            EXTRACT(YEAR FROM AGE(p.dob)) as age,
            p.username AS username, 
            p.about AS about,
            p.status as profile_status,      
            i.secure_url,
            i.status AS image_status, 
            city.name AS city_name, 
            region.name AS region_name, 
            p.status AS status,
            CASE WHEN blockedBy.user_id IS NULL THEN FALSE ELSE TRUE END AS blocked_by_user_id,
            CASE WHEN blocked.blocked_user_id IS NULL THEN FALSE ELSE TRUE END AS blocked_user_id,
            u.last_login AS last_login
            FROM datinglibre.profiles p
            LEFT JOIN datinglibre.images i ON p.user_id = i.user_id AND i.status = 'ACCEPTED' AND i.is_profile IS TRUE
            LEFT JOIN datinglibre.blocks blockedBy ON blockedBy.user_id = p.user_id AND blockedBy.blocked_user_id = :currentUserId
            LEFT JOIN datinglibre.blocks blocked ON blocked.user_id = :currentUserId AND blocked.blocked_user_id = p.user_id    
            INNER JOIN datinglibre.users u ON p.user_id = u.id
            INNER JOIN datinglibre.cities city ON p.city_id = city.id 
            INNER JOIN datinglibre.regions region ON city.region_id = region.id
            WHERE p.user_id = :userId 
EOD, $this->getProjectionResultSetMapping());

        $query->setParameter('userId', $userId);
        $query->setParameter('currentUserId', $currentUserId);

        return $query->getOneOrNullResult();
    }

    public function delete(Profile $profile)
    {
        $this->getEntityManager()->remove($profile);
        $this->getEntityManager()->flush();
    }

    public function findProjectionByUsername(string $username): ?ProfileProjection
    {
        $rsm = $this->getProjectionResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
SELECT p.user_id,
           EXTRACT(YEAR FROM AGE(p.dob)) as age,
           p.username,
           p.about,
           p.meta,
           image.secure_url,
           city.name as city_name,
           region.name as region_name,
           p.status as profile_status,
           image.status as image_status,
           u.last_login as last_login
           FROM datinglibre.profiles p 
           INNER JOIN datinglibre.users u ON u.id = p.user_id
           LEFT JOIN datinglibre.images image ON image.user_id = p.user_id AND image.is_profile IS TRUE 
           LEFT JOIN datinglibre.cities city ON city.id = p.city_id
           LEFT JOIN datinglibre.regions region ON region.id = city.region_id 
           WHERE LOWER(p.username) = LOWER(:username)
EOD, $rsm);

        $query->setParameter('username', $username);
        return $query->getOneOrNullResult();
    }

    public function getProjectionResultSetMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\ProfileProjection', 'pv');
        $rsm->addFieldResult('pv', 'user_id', 'id');
        $rsm->addFieldResult('pv', 'username', 'username');
        $rsm->addFieldResult('pv', 'age', 'age');
        $rsm->addFieldResult('pv', 'about', 'about');
        $rsm->addFieldResult('pv', 'city_name', 'cityName', false);
        $rsm->addFieldResult('pv', 'region_name', 'regionName', false);
        $rsm->addFieldResult('pv', 'last_login', 'lastLogin');
        $rsm->addFieldResult('pv', 'secure_url', 'imageUrl');
        $rsm->addFieldResult('pv', 'profile_status', 'profileStatus');
        $rsm->addFieldResult('pv', 'blocked_by_user_id', 'blockedBy');
        $rsm->addFieldResult('pv', 'blocked_user_id', 'blocked');
        $rsm->addFieldResult('pv', 'image_status', 'imageStatus');
        return $rsm;
    }
}
