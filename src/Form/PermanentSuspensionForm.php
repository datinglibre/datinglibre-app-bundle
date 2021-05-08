<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

class PermanentSuspensionForm
{
    private array $reasons;

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }
}
