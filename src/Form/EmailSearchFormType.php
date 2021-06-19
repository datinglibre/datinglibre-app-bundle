<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, []);
        $builder->add('submit', SubmitType::class, ['label' => 'search.search']);
    }
}
