<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\SubscriptionService;
use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserAccountSubscriptionController extends AbstractController
{
    private SuspensionService $suspensionService;
    private SubscriptionService $subscriptionService;
    private array $paymentProviders;

    public function __construct(
        array $paymentProviders,
        SuspensionService $suspensionService,
        SubscriptionService $subscriptionService
    )
    {
        $this->suspensionService = $suspensionService;
        $this->paymentProviders = $paymentProviders;
        $this->subscriptionService = $subscriptionService;
    }

    public function viewSubscription(): Response
    {
        $userId = $this->getUser()->getId();
        $suspension = $this->suspensionService->findOpenByUserId($this->getUser()->getId());

        return $this->render(
            '@DatingLibreApp/user/account/subscription.html.twig',
            [
                'userId' => $userId,
                'suspension' => $suspension,
                'subscriptions' => $this->subscriptionService->findByUserId($userId),
                'activeSubscription' => $this->subscriptionService->findActiveSubscriptionByUserId($userId),
                'paymentProviders' => $this->paymentProviders
            ]
        );
    }
}
