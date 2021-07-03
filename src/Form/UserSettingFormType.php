<?php

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSettingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'newMatchNotifications',
            CheckboxType::class,
            ['label' => 'account.new_match_notifications', 'required' => false]
        );
        $builder->add('submit', SubmitType::class);
    }
}
