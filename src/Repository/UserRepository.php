<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Repository;

use DatingLibre\AppBundle\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->flush($user);
    }

    public function loadUserByUsername(string $email): ?UserInterface
    {
        return $this->loadUserByIdentifier($email);
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->getEntityManager()->createQuery('SELECT u FROM DatingLibre\AppBundle\Entity\User u WHERE u.email = :email')
            ->setParameter('email', trim(strtolower($identifier)))
            ->getOneOrNullResult();
    }

    public function deleteByEmail(string $email): void
    {
        $user = $this->findOneBy([User::EMAIL => $email]);

        if ($user === null) {
            return;
        }

        $this->_em->remove($user);
        $this->_em->flush();
    }

    public function delete(Uuid $userId): void
    {
        $user = $this->find($userId);

        if ($user === null) {
            return;
        }

        $this->_em->remove($user);
        $this->_em->flush();
    }

    public function updateCreatedAt(Uuid $userId, DateTimeImmutable $dateTime): void
    {
        $query = $this->getEntityManager()->createNativeQuery(<<<EOD
UPDATE datinglibre.users SET created_at = :createdAt WHERE id = :userId
EOD, new ResultSetMapping());

        $query->setParameter('userId', $userId);
        $query->setParameter('createdAt', $dateTime);

        $query->execute();
    }

    public function save(User $user): User
    {
        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }
}
