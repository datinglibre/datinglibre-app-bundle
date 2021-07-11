<?php

namespace DatingLibre\AppBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true)
 */
class ProfileProjection
{
    /**
     * Entity solely used as a projection
     */

    /**
     * @ORM\Column(type="string", length=255, name="id")
     * @ORM\Id()
     */
    private string $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $username;

    /**
     * @ORM\Column(type="integer")
     */
    private int $age;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $about;

    /**
     * @ORM\Column(type="string", name="city_name")
     */
    private string $cityName;

    /**
     * @ORM\Column(type="string", name="region_name")
     */
    private string $regionName;

    /**
     * @ORM\Column(type="datetimetz", name="last_login")
     */
    private DateTimeInterface $lastLogin;

    /**
     * @ORM\Column(type="string", name="profile_status")
     */
    private string $profileStatus;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sortId;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $imageUrl = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $imageStatus = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $blockedBy = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $blocked = false;


    public function getId(): string
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setUsername($username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setAbout($about): void
    {
        $this->about = $about;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setCityName($cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setRegionName($regionName): void
    {
        $this->regionName = $regionName;
    }

    public function getRegionName(): string
    {
        return $this->regionName;
    }

    public function setAge($age): void
    {
        $this->age = $age;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setLastLogin($lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getLastLogin(): DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setProfileStatus(string $profileStatus): void
    {
        $this->profileStatus = $profileStatus;
    }

    public function setSortId($sortId): void
    {
        $this->sortId = $sortId;
    }

    public function getSortId(): int
    {
        return $this->sortId;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageStatus($imageStatus): void
    {
        $this->imageStatus = $imageStatus;
    }

    public function getImageStatus()
    {
        return $this->imageStatus;
    }

    public function isImagePresent()
    {
        return null !== $this->getImageStatus();
    }

    public function isImageUnmoderated(): bool
    {
        return Image::UNMODERATED === $this->getImageStatus();
    }

    public function isImageRejected(): bool
    {
        return Image::REJECTED === $this->getImageStatus();
    }

    public function isPermanentlySuspended(): bool
    {
        return Profile::PERMANENTLY_SUSPENDED === $this->profileStatus;
    }

    public function isSuspended(): bool
    {
        return Profile::SUSPENDED === $this->profileStatus;
    }

    public function setBlockedBy(bool $blockedBy): void
    {
        $this->blockedBy = $blockedBy;
    }

    public function setBlocked(bool $blocked): void
    {
        $this->blocked = $blocked;
    }

    public function isBlockedUser(): bool
    {
        return $this->blocked;
    }

    public function isBlockedByUser(): bool
    {
        return $this->blockedBy;
    }
}
