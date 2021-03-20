<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function save(Subscription $subscription): Subscription
    {
        $this->getEntityManager()->persist($subscription);
        $this->getEntityManager()->flush();

        return $subscription;
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('delete_subscriptions')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
