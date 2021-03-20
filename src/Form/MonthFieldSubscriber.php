<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class MonthFieldSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData', FormEvents::PRE_SUBMIT => 'preSubmit'];
    }

    public function preSetData(FormEvent $event): void
    {
        $eventForm = $event->getData();
        $form = $event->getForm();

        $year = $eventForm->getYear();
        $month = $eventForm->getMonth();

        $this->addDayField($form, $year, $month);
    }

    public function preSubmit(PreSubmitEvent $event): void
    {
        $this->addDayField($event->getForm(), (int) $event->getData()['year'], (int) $event->getData()['month']);
    }


    public function addDayField(FormInterface $form, $year, $month): void
    {
        $form->add(
            'day',
            ChoiceType::class,
            [
                'label' => 'filter.day_optional',
                'placeholder' => '',
                'choices' => range(1, cal_days_in_month(
                    CAL_GREGORIAN,
                    $month,
                    $year
                )),
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'required' => false
            ]
        );
    }
}
