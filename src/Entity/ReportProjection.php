<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(readOnly=true)
 */
class ReportProjection
{
    /**
     * @ORM\Column(type="uuid", name="id")
     * @ORM\Id()
     */
    private Uuid $id;

    /**
     * @ORM\Column(type="uuid", name="reporter_id")
     * @ORM\Id()
     */
    private ?Uuid $reporterId;

    /**
     * @ORM\Column(type="uuid", name="reported_id")
     * @ORM\Id()
     */
    private Uuid $reportedId;

    /**
     * @ORM\Column(type="string")
     */
    private string $reportedUsername;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $reporterUsername;

    /**
     * @ORM\Column(type="string")
     */
    private string $status;

    /**
     * @ORM\Column(type="text[]")
     */
    private array $reasons;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private DateTimeInterface $createdAt;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getReporterId(): ?Uuid
    {
        return $this->reporterId;
    }

    public function setReporterId(?Uuid $reporterId): void
    {
        $this->reporterId = $reporterId;
    }

    public function getReporterUsername(): ?string
    {
        return $this->reporterUsername;
    }

    public function setReporterUsername(?string $reporterUsername): void
    {
        $this->reporterUsername = $reporterUsername;
    }

    public function getReportedId(): Uuid
    {
        return $this->reportedId;
    }

    public function setReportedId(Uuid $reportedId): void
    {
        $this->reportedId = $reportedId;
    }

    public function getReportedUsername(): string
    {
        return $this->reportedUsername;
    }

    public function setReportedUsername(string $reportedUsername): void
    {
        $this->reportedUsername = $reportedUsername;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
