<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\RegionRepository")
 * @ORM\Table(name="datinglibre.regions")
 */
class Region
{
    public const NAME = 'name';

    /**
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @ORM\Column(type="uuid")
     */
    private Uuid $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ManyToOne(targetEntity="Country", inversedBy="regions")
     * @JoinColumn(name = "country_id", referencedColumnName = "id")
     */
    private Country $country;

    /**
     * @OneToMany(targetEntity="City", mappedBy="region")
     */
    private Collection $cities;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setCountry(Country $country): Region
    {
        $this->country = $country;
        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setName($name): Region
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function setCities($cities)
    {
        $this->cities = $cities;
        return $this;
    }
}
