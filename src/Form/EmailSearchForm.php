<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

class EmailSearchForm
{
    private string $email;

    public function getEmail(): string
    {
        return strtolower($this->email);
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
