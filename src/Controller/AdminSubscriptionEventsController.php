<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\EventFilterForm;
use DatingLibre\AppBundle\Form\EventFilterFormType;
use DatingLibre\AppBundle\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSubscriptionEventsController extends AbstractController
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function index(Request $request): Response
    {
        $eventForm = new EventFilterForm();
        $eventFormType = $this->createForm(EventFilterFormType::class, $eventForm);
        $eventFormType->handleRequest($request);

        $events = $this->getEvents($eventForm->getYear(), $eventForm->getMonth(), $eventForm->getDay());

        return $this->render('@DatingLibreApp/admin/subscription/events/index.html.twig', [
            'events' => $events,
            'eventFilterForm' => $eventFormType->createView()
        ]);
    }

    private function getEvents(int $year, int $month, ?int $day): array
    {
        if ($day === null) {
            return $this->eventRepository->findByMonth($year, $month);
        }

        return $this->eventRepository->findByDay($year, $month, $day);
    }
}
