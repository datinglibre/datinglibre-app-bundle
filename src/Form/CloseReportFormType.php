<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CloseReportFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => CloseReportForm::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'report',
            HiddenType::class,
            [
                'property_path' => 'report.id',
                'mapped' => false
            ]
        );

        $builder->add('submit', SubmitType::class, ['label' => 'report.confirm_close']);
    }
}