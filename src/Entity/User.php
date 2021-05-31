<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\UserRepository")
 * @ORM\Table(name="datinglibre.users")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    public const EMAIL = 'email';
    public const ENABLED = 'enabled';
    public const MODERATOR = 'ROLE_MODERATOR';
    public const ADMIN = 'ROLE_ADMIN';
    public const USER = 'ROLE_USER';

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
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var DateTime Created at
     * @ORM\Column(name="created_at", type="datetimetz")
     */
    private $createdAt;

    /**
     * @var boolean Is user enabled
     * @ORM\Column(type="boolean")
     */
    private $enabled = false;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $lastLogin;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @ORM\PrePersist
     */
    public function addCreatedAt()
    {
        $this->createdAt = new DateTime('UTC');
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    public function setLastLogin(DateTimeInterface $lastLogin)
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getLastLogin()
    {
        return $this->lastLogin;
    }
}
