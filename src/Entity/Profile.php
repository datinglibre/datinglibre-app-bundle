<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Table(name="datinglibre.profiles")
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\ProfileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Profile
{
    // moderation statuses
    public const UNMODERATED = 'UNMODERATED';
    public const PASSED = 'PASSED';

    // subscription statuses
    public const NO_SUBSCRIPTION = 'NONE';

    public function __construct()
    {
        $this->about = null;
        $this->city = null;
        $this->username = null;
        $this->dob = null;
        $this->subscriptionStatus = self::NO_SUBSCRIPTION;
        $this->moderationStatus = self::UNMODERATED;
    }

    /**
     * @ORM\Id()
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private User $user;

    /**
     * @OneToOne(targetEntity="DatingLibre\AppBundle\Entity\City")
     * @JoinColumn(name = "city_id", referencedColumnName = "id")
     */
    private ?City $city;

    /**
     * @ORM\Column(type="string", name="username")
     */
    private ?string $username;

    /**
     * @ORM\Column(type="string", name="about")
     */
    private ?string $about;

    /**
     * @ORM\Column(type="date", name="dob")
     */
    private ?DateTimeInterface $dob;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz")
     */
    private DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(name="moderation_status", type="string")
     */
    private string $moderationStatus;

    /**
     * @ORM\Column(name="subscription_status", type="string")
     */
    private string $subscriptionStatus;


    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Profile
    {
        $this->user = $user;
        return $this;
    }

    public function setCity(City $city): Profile
    {
        $this->city = $city;
        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setAbout(?string $about): void
    {
        $this->about = $about;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setDob($dob)
    {
        $this->dob = $dob;
        return $this;
    }

    public function getDob(): ?DateTimeInterface
    {
        return $this->dob;
    }

    public function getSubscriptionStatus(): string
    {
        return $this->subscriptionStatus;
    }

    public function setSubscriptionStatus(string $subscriptionStatus): void
    {
        $this->subscriptionStatus = $subscriptionStatus;
    }

    public function getModerationStatus(): string
    {
        return $this->moderationStatus;
    }

    public function setModerationStatus($moderationStatus): void
    {
        $this->moderationStatus = $moderationStatus;
    }

    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
        $this->updatedAt = new DateTime('UTC');
        $this->moderationStatus = $this->moderationStatus === null ? self::UNMODERATED : $this->moderationStatus;
    }
}
