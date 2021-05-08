<?php

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\ImageRepository")
 * @ORM\Table(name="datinglibre.images")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    // statuses
    public const UNMODERATED = 'UNMODERATED';
    public const ACCEPTED = 'ACCEPTED';
    public const REJECTED = 'REJECTED';

    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     * @ORM\Column(type="uuid")
     */
    private $id;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private $user;

    /**
     * @ORM\Column(type = "string")
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isProfile;

    /**
     * @ORM\Column(name="secure_url", type="string")
     */
    private $secureUrl;

    /**
     * @ORM\Column(name="secure_url_expiry", type = "datetimetz")
     */
    private $secureUrlExpiry;

    /**
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    /**
     * @ORM\Column(name="created_at", type="datetimetz")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz")
     */
    private $updatedAt;


    public function getType()
    {
        return $this->type;
    }

    public function getSecureUrlExpiry()
    {
        return $this->secureUrlExpiry;
    }

    public function setSecureUrlExpiry($secureUrlExpiry): void
    {
        $this->secureUrlExpiry = $secureUrlExpiry;
    }

    public function setType($type): Image
    {
        $this->type = $type;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->getId() . '.' . $this->getType();
    }

    public function setSecureUrl(string $secureUrl): self
    {
        $this->secureUrl = $secureUrl;
        return $this;
    }

    public function getSecureUrl()
    {
        return $this->secureUrl;
    }

    public function getIsProfile()
    {
        return $this->isProfile;
    }

    public function setIsProfile($isProfile): void
    {
        $this->isProfile = $isProfile;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new DateTime('UTC');
        $this->updatedAt = new DateTime('UTC');
        $this->status = self::UNMODERATED;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime('UTC');
    }
}
