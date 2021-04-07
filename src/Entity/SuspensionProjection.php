<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(readOnly=true)
 */
class SuspensionProjection
{
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     */
    private Uuid $id;

    /**
     * @ORM\Column(type="uuid")
     */
    private Uuid $userId;

    /**
     * @ORM\Column(type="string")
     */
    private string $username;

    /**
     * @ORM\Column(type="json")
     */
    private array $reasons;

    /**
     * @ORM\Column(type="integer")
     */
    private int $duration;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $elapsed;

    /**
     * @ORM\Column(type="string")
     */
    private string $status;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private DateTimeInterface $createdAt;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(Uuid $userId): void
    {
        $this->userId = $userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
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

    public function isElapsed(): bool
    {
        return $this->elapsed;
    }

    public function setElapsed(bool $elapsed): void
    {
        $this->elapsed = $elapsed;
    }

    public function isStatus(): string
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

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
