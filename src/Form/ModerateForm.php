<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

class ModerateForm
{
    private $id;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }
}
