<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use DatingLibre\AppBundle\Entity\Attribute;
use DatingLibre\AppBundle\Entity\City;
use DatingLibre\AppBundle\Entity\Country;
use DatingLibre\AppBundle\Entity\Region;

class ProfileForm
{
    private $username;
    private $about;
    private $dob;
    private ?Attribute $color;
    private ?Attribute $shape;
    private array $interests;
    private ?City $city;
    private ?Region $region;
    private ?Country $country;

    public function __construct()
    {
        $this->interests = [];
        $this->city = null;
        $this->region = null;
        $this->country = null;
        $this->color = null;
        $this->shape = null;
    }

    public function setUsername($username): ProfileForm
    {
        $this->username = $username;
        return $this;
    }

    public function setAbout($about): ProfileForm
    {
        $this->about = $about;
        return $this;
    }

    public function setCity(?City $city): ProfileForm
    {
        $this->city = $city;
        return $this;
    }

    public function setRegion(?Region $region): ProfileForm
    {
        $this->region = $region;
        return $this;
    }

    public function setCountry(?Country $country): ProfileForm
    {
        $this->country = $country;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setDob($dob): self
    {
        $this->dob = $dob;
        return $this;
    }

    public function getDob()
    {
        return $this->dob;
    }

    public function getColor(): ?Attribute
    {
        return $this->color;
    }

    public function setColor(?Attribute $color): void
    {
        $this->color = $color;
    }

    public function getShape(): ?Attribute
    {
        return $this->shape;
    }

    public function setShape(?Attribute $shape): void
    {
        $this->shape = $shape;
    }

    public function setInterests(array $interests)
    {
        $this->interests = $interests;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }
}
