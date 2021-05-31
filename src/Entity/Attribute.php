<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\AttributeRepository")
 * @ORM\Table(name="datinglibre.attributes")
 */
class Attribute
{
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="attributes")
     * @JoinColumn(name = "category_id", referencedColumnName = "id")
     */
    private $category;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory($category): void
    {
        $this->category = $category;
    }
}
