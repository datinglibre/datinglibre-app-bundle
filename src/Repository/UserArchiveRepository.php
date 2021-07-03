<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DateTimeInterface;
use DatingLibre\AppBundle\Entity\UserArchive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserArchive|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserArchive|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserArchive[]    findAll()
 * @method UserArchive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserArchive::class);
    }
    public function save(UserArchive $userArchive): UserArchive
    {
        $this->getEntityManager()->persist($userArchive);
        $this->getEntityManager()->flush();

        return $userArchive;
    }

    public function deleteByEmail(string $email)
    {
        $userArchive = $this->findOneBy(['email' => $email]);

        if ($userArchive === null) {
            return;
        }

        $this->getEntityManager()->remove($userArchive);
        $this->getEntityManager()->flush();
    }

    public function deleteOlderThanDays(int $days)
    {
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
DELETE FROM datinglibre.user_archives ua WHERE created_at < NOW() - :days * INTERVAL '1 DAY'
EOD, new ResultSetMapping());

        $query->setParameter('days', $days);
        $query->execute();
    }

    public function updateCreatedAt(string $email, DateTimeInterface $dateTime): void
    {
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
UPDATE datinglibre.user_archives SET created_at = :createdAt WHERE email = :email
EOD, new ResultSetMapping());

        $query->setParameter('email', $email);
        $query->setParameter('createdAt', $dateTime);

        $query->execute();
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('delete_user_archives')
            ->delete()
            ->getQuery()
            ->execute();
    }
}