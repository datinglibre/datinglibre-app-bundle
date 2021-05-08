<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function buildForm(FormBuilderInterface $blockForm, array $options)
    {
        $blockForm->add('confirm', SubmitType::class, ['label' => 'block.block']);
    }
}
