<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DateTimeInterface;
use DatingLibre\AppBundle\Entity\Event;
use DatingLibre\AppBundle\Entity\Subscription;
use DatingLibre\AppBundle\Repository\SubscriptionRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;

class SubscriptionService
{
    private SubscriptionRepository $subscriptionRepository;
    private UserRepository $userRepository;
    private EventService $eventService;
    protected const USER_MISSING = 'Could not find user';
    protected const NO_EXISTING_SUBSCRIPTION_FOUND = 'No existing subscription found';

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        UserRepository $userRepository,
        EventService $eventService
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
        $this->eventService = $eventService;
    }

    public function create(
        ?Uuid $userId,
        string $provider,
        string $providerId,
        string $event,
        DateTimeInterface $renewalDate,
        array $data
    ): void {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if ($user === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::USER_MISSING => $data]);
            return;
        }

        $this->eventService->save($user->getId(), $event, $data);
        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setProvider($provider);
        $subscription->setState(Subscription::ACTIVE);
        $subscription->setProviderSubscriptionId($providerId);
        $subscription->setRenewalDate($renewalDate);
        $this->subscriptionRepository->save($subscription);
    }

    public function findByUserId($id): array
    {
        return $this->subscriptionRepository->findBy(['user' => $id]);
    }

    public function renew(
        string $provider,
        ?string $providerSubscriptionId,
        string $event,
        DateTimeInterface $nextRenewalDate,
        array $data
    ): void {
        $subscription = $this->getSubscription($provider, $providerSubscriptionId, $data);

        if ($subscription === null) {
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setRenewalDate($nextRenewalDate);
        $this->subscriptionRepository->save($subscription);
    }

    public function cancel(string $provider, ?string $providerSubscriptionId, string $event, array $data): void
    {
        $subscription = $this->getSubscription($provider, $providerSubscriptionId, $data);

        if ($subscription === null) {
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setState(Subscription::CANCELLED);
        $subscription->setRenewalDate(null);
        $this->subscriptionRepository->save($subscription);
    }

    public function failRenewal(
        string $provider,
        ?string $providerSubscriptionId,
        string $event,
        ?DateTimeInterface $nextRetryDate,
        array $data
    ): void {
        $subscription = $this->getSubscription($provider, $providerSubscriptionId, $data);

        if ($subscription === null) {
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setState(Subscription::RENEWAL_FAILURE);

        if ($nextRetryDate !== null) {
            $subscription->setRenewalDate($nextRetryDate);
        }

        $this->subscriptionRepository->save($subscription);
    }

    public function chargeback(string $provider, ?string $providerSubscriptionId, string $event, array $data)
    {
        $subscription = $this->getSubscription($provider, $providerSubscriptionId, $data);

        if ($subscription === null) {
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setState(Subscription::CHARGEBACK);
        $subscription->setRenewalDate(null);
        $this->subscriptionRepository->save($subscription);
    }

    public function refund(string $provider, ?string $providerSubscriptionId, string $event, array $data)
    {
        $subscription = $this->getSubscription($provider, $providerSubscriptionId, $data);

        if ($subscription === null) {
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setState(Subscription::REFUND);
        $subscription->setRenewalDate(null);
        $this->subscriptionRepository->save($subscription);
    }

    private function saveNoExistingSubscriptionErrorEvent(array $data): void
    {
        $this->eventService->save(
            null,
            Event::SUBSCRIPTION_ERROR,
            [
                self::NO_EXISTING_SUBSCRIPTION_FOUND => $data
            ]
        );
    }

    public function changeBillingDate(
        string $provider,
        ?string $providerSubscriptionId,
        string $event,
        DateTimeInterface $nextBillingDate,
        array $data
    ): void {
        $subscription = $this->getSubscription($provider, $providerSubscriptionId, $data);

        if ($subscription === null) {
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setRenewalDate($nextBillingDate);
        $this->subscriptionRepository->save($subscription);
    }

    private function getSubscription(string $provider, ?string $providerSubscriptionId, array $data): ?Subscription
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            Subscription::PROVIDER_SUBSCRIPTION_ID => $providerSubscriptionId,
            Subscription::PROVIDER => $provider
        ]);

        if ($subscription === null) {
            $this->saveNoExistingSubscriptionErrorEvent($data);
        }

        return $subscription;
    }
}
