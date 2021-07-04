<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserAccountSubscriptionController extends AbstractController
{
    private SubscriptionService $subscriptionService;
    private array $paymentProviders;

    public function __construct(array $paymentProviders, SubscriptionService $subscriptionService)
    {
        $this->paymentProviders = $paymentProviders;
        $this->subscriptionService = $subscriptionService;
    }

    public function viewSubscription(): Response
    {
        $userId = $this->getUser()->getId();

        return $this->render(
            '@DatingLibreApp/user/account/subscription.html.twig',
            [
                'userId' => $userId,
                'subscriptions' => $this->subscriptionService->findByUserId($userId),
                'activeSubscription' => $this->subscriptionService->findActiveSubscriptionByUserId($userId),
                'paymentProviders' => $this->paymentProviders
            ]
        );
    }
}
