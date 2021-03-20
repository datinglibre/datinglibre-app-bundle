<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFilterFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => EventFilterForm::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'year',
            ChoiceType::class,
            [
                'choices' => range(2020, (new DateTimeImmutable('now'))->format('Y')),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ]
        );

        $builder->add(
            'month',
            ChoiceType::class,
            [
                'choices' => range(1, 12),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ]
        );

        $builder->addEventSubscriber(new MonthFieldSubscriber());

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => 'event.filter'
            ]
        );
    }
}
