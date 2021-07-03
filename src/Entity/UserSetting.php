<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Table(name="datinglibre.user_settings")
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\UserSettingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class UserSetting
{
    /**
     * @ORM\Id()
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(name="new_match_notifications", type="boolean")
     */
    private bool $newMatchNotifications = true;

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

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function isNewMatchNotifications(): bool
    {
        return $this->newMatchNotifications;
    }

    public function setNewMatchNotifications(bool $newMatchNotifications): void
    {
        $this->newMatchNotifications = $newMatchNotifications;
    }
}
