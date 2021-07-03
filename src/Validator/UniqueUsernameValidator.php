<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Validator;

use DatingLibre\AppBundle\Repository\ProfileRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueUsernameValidator extends ConstraintValidator
{
    private ProfileRepository $profileRepository;
    private Security $security;

    public function __construct(ProfileRepository $profileRepository, Security $security)
    {
        $this->profileRepository = $profileRepository;
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueUsername) {
            throw new UnexpectedTypeException($constraint, UniqueUsername::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $profileProjection = $this->profileRepository->findProjectionByUsername($value);

        if ($profileProjection !== null
            && !Uuid::fromString($profileProjection->getId())->equals($this->security->getUser()->getId())) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
