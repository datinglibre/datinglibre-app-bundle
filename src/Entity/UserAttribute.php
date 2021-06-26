<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\UserAttributeRepository")
 * @ORM\Table(name="datinglibre.user_attributes")

 */
class UserAttribute
{
    /**
     * @Id()
     * @OneToOne(targetEntity="Attribute")
     * @JoinColumn(name = "attribute_id", referencedColumnName = "id")
     */
    private Attribute $attribute;

    /**
     * @Id()
     * @OneToOne(targetEntity="user")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }
}
