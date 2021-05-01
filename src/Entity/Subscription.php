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
    public const STATE = 'state';
    // states as below
    public const ACTIVE = 'active';
    public const CANCELLED = 'cancelled';
    public const RENEWAL_FAILURE = 'renewal_failure';
    public const CHARGEBACK = 'chargeback';
    public const REFUND = 'refund';

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
     * @ORM\Column(name="state", type="text")
     */
    private string $state;

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

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }
}
