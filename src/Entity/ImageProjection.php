<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity solely used as a projection
 */

/**
 * @ORM\Entity(readOnly=true)
 */
class ImageProjection
{
    /**
     * @ORM\Column(type="string", length=255, name="id")
     * @ORM\Id()
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $secureUrl;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $secureUrlExpiry;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getSecureUrl()
    {
        return $this->secureUrl;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    public function setSecureUrl($secureUrl)
    {
        $this->secureUrl = $secureUrl;
        return $this;
    }

    public function getSecureUrlExpiry(): DateTimeInterface
    {
        return $this->secureUrlExpiry;
    }

    public function setSecureUrlExpiry($secureUrlExpiry): void
    {
        $this->secureUrlExpiry = $secureUrlExpiry;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($state): void
    {
        $this->status = $state;
    }
}
