<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

class ReportForm
{
    private array $reasons;
    private ?string $message;

    public function __construct()
    {
        $this->message = null;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }
}
