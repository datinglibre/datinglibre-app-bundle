<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\BlockRepository")
 * @ORM\Table(name="datinglibre.blocks")
 */
class Block
{
    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     * @ORM\Column(type="uuid")
     */
    private Uuid $id;

    /**
     * @ManyToOne(targetEntity="user")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private User $user;

    /**
     * @OneToOne(targetEntity="user")
     * @JoinColumn(name = "blocked_user_id", referencedColumnName = "id")
     */
    private User $blockedUser;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setUser($user): Block
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setBlockedUser($blockedUser): Block
    {
        $this->blockedUser = $blockedUser;
        return $this;
    }

    public function getBlockedUser(): User
    {
        return $this->blockedUser;
    }
}
