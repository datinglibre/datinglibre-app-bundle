<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\SubscriptionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountSubscriptionController extends AbstractController
{
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function viewSubscription(Request $request)
    {
        return $this->render(
            '@DatingLibreApp/account/subscription.html.twig',
            [
                'controller_name' => 'AccountSubscriptionController',
                'subscriptions' => $this->subscriptionService->findByUserId($this->getUser()->getId())
            ]
        );
    }
}
