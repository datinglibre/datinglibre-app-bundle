<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\SubscriptionRepository")
 * @ORM\Table(name="datinglibre.subscriptions")
 * @ORM\HasLifecycleCallbacks
 */
class Subscription
{
    public const CCBILL = 'ccbill';
    public const PROVIDER = 'provider';
    public const PROVIDER_SUBSCRIPTION_ID = 'providerSubscriptionId';
    public const STATUS = 'status';
    // states as below
    public const ACTIVE = 'ACTIVE';
    public const CANCELLED = 'CANCELLED';
    public const RENEWAL_FAILURE = 'RENEWAL_FAILURE';
    public const CHARGEBACK = 'CHARGEBACK';
    public const REFUND = 'REFUND';

    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private ?User $user;

    /**
     * @ORM\Column(name="provider", type="text")
     */
    private string $provider;

    /**
     * @ORM\Column(name="provider_subscription_id", type="text")
     */
    private string $providerSubscriptionId;

    /**
     * @ORM\Column(name="status", type="text")
     */
    private string $status;

    /**
     * @ORM\Column(name="renewal_date", type="date")
     */
    private ?DateTimeInterface $renewalDate;

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

    public function setUser($user): Subscription
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
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


    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getProviderSubscriptionId(): string
    {
        return $this->providerSubscriptionId;
    }

    public function getRenewalDate(): ?DateTimeInterface
    {
        return $this->renewalDate;
    }

    public function setRenewalDate(?DateTimeInterface $renewalDate): void
    {
        $this->renewalDate = $renewalDate;
    }

    public function setProviderSubscriptionId(string $providerSubscriptionId): void
    {
        $this->providerSubscriptionId = $providerSubscriptionId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isActive(): bool
    {
        return self::ACTIVE === $this->getStatus();
    }

    public function isRenewalFailure(): bool
    {
        return self::RENEWAL_FAILURE === $this->getStatus();
    }
}
