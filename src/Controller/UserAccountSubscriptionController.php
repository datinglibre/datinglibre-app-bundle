<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserAccountSubscriptionController extends AbstractController
{
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function viewSubscription(): Response
    {
        return $this->render(
            '@DatingLibreApp/user/account/subscription.html.twig',
            [
                'controller_name' => 'AccountSubscriptionController',
                'subscriptions' => $this->subscriptionService->findByUserId($this->getUser()->getId())
            ]
        );
    }
}
