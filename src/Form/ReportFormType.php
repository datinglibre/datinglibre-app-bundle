<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportFormType extends AbstractType
{
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ReportForm::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reasons', ChoiceType::class, [
            'choices' => array_combine($this->rules, $this->rules),
            'choice_translation_domain' => 'rules',
            'required' => true,
            'expanded' => true,
            'multiple' => true,
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('message', TextareaType::class, [
            'label' => 'report.message',
            'required' => false
        ]);

        $builder->add('submit', SubmitType::class, ['label' => 'report.report']);
    }
}
