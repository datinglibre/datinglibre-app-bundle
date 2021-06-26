<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Table(name="datinglibre.requirements")
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\RequirementRepository")
 */
class Requirement
{
    /**
     * @Id()
     * @OneToOne(targetEntity="Attribute")
     * @JoinColumn(name = "attribute_id", referencedColumnName = "id")
     */
    private $attribute;

    /**
     * @Id()
     * @OneToOne(targetEntity="user")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private $user;

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }
}
