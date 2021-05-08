<?php

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PermanentSuspensionFormType extends AbstractType
{
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'user',
            HiddenType::class,
            [
                'property_path' => 'user.id',
                'mapped' => false
            ]
        );

        $builder->add('reasons', ChoiceType::class, [
            // creates a key that matches the value
            'choices' => array_combine($this->rules, $this->rules),
            'choice_translation_domain' => 'rules',
            'required' => true,
            'expanded' => true,
            'multiple' => true,
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('submit', SubmitType::class, ['label' => 'suspension.confirm_enqueue_permanent_suspension']);
    }
}
