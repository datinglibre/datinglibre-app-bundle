<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use Behat\Behat\Context\Context;
use DatingLibre\AppBundle\Entity\Subscription;
use DatingLibre\AppBundle\Repository\SubscriptionRepository;
use DatingLibre\AppBundle\Service\UserService;
use Webmozart\Assert\Assert;

class SubscriptionContext implements Context
{
    private UserService $userService;
    private SubscriptionRepository $subscriptionRepository;

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->subscriptionRepository->deleteAll();
    }

    public function __construct(UserService $userService, SubscriptionRepository $subscriptionRepository)
    {
        $this->userService = $userService;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @Then a new :provider subscription is created for :email with provider subscription ID :providerSubscriptionId
     */
    public function aNewSubscriptionIsCreatedForUserWithProviderId(
        string $provider,
        string $email,
        string $providerSubscriptionId
    ): void {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $subscription = $this->subscriptionRepository->findOneBy(
            [
                'user' => $user,
                'provider' => $provider,
                'providerSubscriptionId' => $providerSubscriptionId
            ]
        );

        Assert::notNull($subscription);
    }

    /**
     * @Then the subscription for :email is cancelled
     */
    public function subscriptionIsCancelled(string $email)
    {
        $user = $this->userService->findByEmail($email);

        $subscription = $this->subscriptionRepository->findOneBy(['user' => $user]);

        Assert::notNull($subscription);
        Assert::true($subscription->isCancelled());
    }

    /**
     * @Given the user :email has a :provider subscription with ID :providerSubscriptionId
     */
    public function theUserHasASubscriptionWithID(string $email, string $provider, string $providerSubscriptionId)
    {
        $user = $this->userService->findByEmail($email);

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setProvider($provider);
        $subscription->setProviderSubscriptionId($providerSubscriptionId);
        $subscription->setStatus(Subscription::ACTIVE);

        $this->subscriptionRepository->save($subscription);
    }
}
