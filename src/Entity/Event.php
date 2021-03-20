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
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\EventRepository")
 * @ORM\Table(name="datinglibre.events")
 * @ORM\HasLifecycleCallbacks
 */
class Event
{
    public const SUBSCRIPTION_ERROR = 'datinglibre.subscription.error';
    public const CCBILL_NEW_SALE = 'datinglibre.ccbill.newsale';
    public const CCBILL_NEW_SALE_FAILURE = 'datinglibre.ccbill.newsalefailure';
    public const CCBILL_RENEWAL = 'datinglibre.ccbill.renewal';
    public const CCBILL_CANCELLATION = 'datinglibre.ccbill.cancellation';
    public const CCBILL_RENEWAL_FAILURE = 'datinglibre.ccbill.renewal.failure';
    public const CCBILL_CHARGEBACK = 'datinglibre.ccbill.chargeback';
    public const CCBILL_REFUND = 'datinglibre.ccbill.refund';
    public const CCILL_BILLING_DATE_CHANGE = 'datinglibre.ccbill.billing.date.change';

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
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(name="data", type="json", options={"jsonb": true})
     */
    private array $data;

    /**
     * @ORM\Column(name="created_at", type="datetimetz")
     */
    private DateTimeInterface $createdAt;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setUser($user): Event
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime('UTC');
    }
}
