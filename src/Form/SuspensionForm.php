<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

class SuspensionForm
{
    private array $reasons;
    private int $duration;

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }
}
