<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use DatingLibre\AppBundle\Entity\Suspension;

class CloseSuspensionForm
{
    private Suspension $suspension;

    public function getSuspension(): Suspension
    {
        return $this->suspension;
    }

    public function setSuspension(Suspension $suspension): void
    {
        $this->suspension = $suspension;
    }
}
