<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use DatingLibre\AppBundle\Repository\SuspensionRepository;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=SuspensionRepository::class)
 * @ORM\Table(name="datinglibre.suspensions")
 * @ORM\HasLifecycleCallbacks
 */
class Suspension
{
    public const OPEN = 'OPEN';
    public const CLOSED = 'CLOSED';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @ORM\Column(type="uuid")
     */
    private Uuid $id;

    /**
     * @ORM\OneToOne(targetEntity="user")
     * @ORM\JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private User $user;

    /**
     * @ORM\OneToOne(targetEntity="user")
     * @ORM\JoinColumn(name = "user_opened_id", referencedColumnName = "id")
     */
    private User $userOpened;

    /**
     * @ORM\OneToOne(targetEntity="user")
     * @ORM\JoinColumn(name = "user_closed_id", referencedColumnName = "id")
     */
    private User $userClosed;

    /**
     * @ORM\Column(name="duration", type="integer")
     */
    private ?int $duration;

    /**
     * @ORM\Column(name="reasons", type="json")
     */
    private array $reasons;

    /**
     * @ORM\Column(name="status", type="string")
     */
    private string $status = self::OPEN;

    /**
     * @ORM\Column(name="created_at", type="datetimetz")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz")
     */
    private DateTimeInterface $updatedAt;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime('UTC');
        $this->updatedAt = new DateTime('UTC');
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime('UTC');
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUserOpened(): User
    {
        return $this->userOpened;
    }

    public function setUserOpened(User $moderator): void
    {
        $this->userOpened = $moderator;
    }

    public function getUserClosed(): User
    {
        return $this->userClosed;
    }

    public function setUserClosed(User $userClosed): void
    {
        $this->userClosed = $userClosed;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
}
