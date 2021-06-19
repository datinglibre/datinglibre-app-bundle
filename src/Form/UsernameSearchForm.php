<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

class UsernameSearchForm
{
    private string $username;

    public function getUsername(): string
    {
        return trim(strtolower($this->username));
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
