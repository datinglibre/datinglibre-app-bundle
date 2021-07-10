<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $event): Event
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        return $event;
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('delete_events')
            ->delete()
            ->getQuery()
            ->execute();
    }

    public function findByMonth(int $year, int $month): array
    {
        $rsm = $this->getEventResultSetMapping();
        $sql = 'SELECT id, name, data, created_at 
                    FROM datinglibre.events 
                    WHERE EXTRACT (YEAR FROM created_at) = :year
                          AND EXTRACT(MONTH FROM created_at) = :month 
                    ORDER BY created_at DESC';
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('year', $year);
        $query->setParameter('month', $month);

        return $query->getResult();
    }

    public function findByDay(int $year, int $month, int $day): array
    {
        $rsm = $this->getEventResultSetMapping();
        $sql = 'SELECT id, name, data, created_at 
                    FROM datinglibre.events 
                    WHERE created_at::date = :date
                    ORDER BY created_at DESC';
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('date', $year . '-' . $month . '-' . $day);

        return $query->getResult();
    }

    public function getEventResultSetMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DatingLibre\AppBundle\Entity\Event', 'e');
        $rsm->addFieldResult('e', 'id', 'id');
        $rsm->addFieldResult('e', 'name', 'name');
        $rsm->addFieldResult('e', 'data', 'data');
        $rsm->addFieldResult('e', 'created_at', 'createdAt');
        return $rsm;
    }
}
