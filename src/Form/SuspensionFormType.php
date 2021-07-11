<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuspensionFormType extends AbstractType
{
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => SuspensionForm::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('duration', ChoiceType::class, [
            // keys are also values
            'choices' => [24 => 24, 48 => 48, 72 => 72],
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
            'label' => 'suspension.duration'
        ]);

        $builder->add('reasons', ChoiceType::class, [
            // creates a key that matches the value
            'choices' => array_combine($this->rules, $this->rules),
            'choice_translation_domain' => 'rules',
            'required' => true,
            'expanded' => true,
            'multiple' => true,
            'constraints' => [
                new NotBlank()
            ],
            'label' => 'suspension.reasons'
        ]);

        $builder->add('submit', SubmitType::class, ['label' => 'suspension.suspend']);
    }
}
