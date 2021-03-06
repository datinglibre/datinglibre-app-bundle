<?php

namespace DatingLibre\AppBundle\Entity;

use DatingLibre\AppBundle\Repository\EmailRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass=EmailRepository::class)
 * @ORM\Table(name="datinglibre.emails")
 * @ORM\HasLifecycleCallbacks
 */
class Email
{
    public const SIGNUP = 'SIGNUP';
    public const PASSWORD_RESET = 'PASSWORD_RESET';
    public const ALREADY_EXISTS = 'ALREADY_EXISTS';
    public const PERMANENT_SUSPENSION = 'PERMANENT_SUSPENSION';
    public const SUSPENSION = 'SUSPENSION';

    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="user")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private $user;

    /**
     * @Orm\Column(type = "string")
     */
    private $type;

    /**
     * @ORM\Column(name="created_at", type="datetimetz")
     */
    private $createdAt;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime('UTC');
    }
}
