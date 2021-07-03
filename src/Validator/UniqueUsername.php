<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUsername extends Constraint
{
    public string $message = 'Username has been taken';
}
