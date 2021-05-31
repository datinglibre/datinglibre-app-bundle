<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\ReportRepository")
 * @ORM\Table(name="datinglibre.reports")
 * @ORM\HasLifecycleCallbacks
 */
class Report
{
    public const OPEN = 'OPEN';
    public const CLOSED = 'CLOSED';

    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @ORM\Column(type="uuid")
     */
    private Uuid $id;

    /**
     * @ManyToOne(targetEntity="user")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private User $user;

    /**
     * @OneToOne(targetEntity="user")
     * @JoinColumn(name = "reported_user_id", referencedColumnName = "id")
     */
    private User $reportedUser;

    /**
     * @OneToOne(targetEntity="user")
     * @JoinColumn(name = "user_closed_id", referencedColumnName = "id")
     */
    private User $userClosed;

    /**
     * @ORM\Column(name="reasons", type="json", options={"jsonb": true})
     */
    private array $reasons;

    /**
     * @ORM\Column(name="message", type="text")
     */
    private string $message;

    /**
     * @ORM\Column(name="status", type="text")
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

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function setUser($user): Report
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getReportedUser(): User
    {
        return $this->reportedUser;
    }

    public function setReportedUser(User $reportedUser): void
    {
        $this->reportedUser = $reportedUser;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getUserClosed(): User
    {
        return $this->userClosed;
    }

    public function setUserClosed(User $userClosed): void
    {
        $this->userClosed = $userClosed;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

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
}
