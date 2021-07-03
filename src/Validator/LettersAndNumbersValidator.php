<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Validator;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LettersAndNumbersValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LettersAndNumbers) {
            throw new UnexpectedTypeException($constraint, LettersAndNumbers::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
