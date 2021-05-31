<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Uid\Uuid;

class PasswordUpdateForm
{
    private string $secret;
    private string $userId;
    private string $password;
    private string $confirmedPassword;

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getConfirmedPassword()
    {
        return $this->confirmedPassword;
    }

    public function setConfirmedPassword($confirmedPassword): void
    {
        $this->confirmedPassword = $confirmedPassword;
    }
}
