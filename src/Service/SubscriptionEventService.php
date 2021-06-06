<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DateTimeImmutable;
use DateTimeInterface;
use DatingLibre\AppBundle\Entity\Event;
use DatingLibre\AppBundle\Entity\Subscription;
use DatingLibre\AppBundle\Repository\UserRepository;
use Exception;
use Symfony\Component\Uid\Uuid;

/**
 * This class processes events initiated from the subscription provider,
 * e.g. the user has cancelled their subscription with the provider.
 *
 * To interact with subscriptions manually, see the SubscriptionService.
 */
class SubscriptionEventService
{
    private SubscriptionService $subscriptionService;
    private UserRepository $userRepository;
    private EventService $eventService;
    private const MISSING_USER = 'Could not find user';
    private const NO_SUBSCRIPTION_FOUND = 'No existing subscription found';

    public function __construct(UserRepository $userRepository, EventService $eventService, SubscriptionService $subscriptionService)
    {
        $this->userRepository = $userRepository;
        $this->eventService = $eventService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @throws Exception
     */
    public function create(Uuid $userId, string $provider, string $providerId, string $event, DateTimeInterface $renewalDate, DateTimeInterface $expiryDate, array $data): void
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if ($user === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::MISSING_USER => $data]);
            throw new Exception(sprintf('Could not find user from ID: %s', $userId->toRfc4122()));
        }

        $this->eventService->save($user->getId(), $event, $data);
        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setProvider($provider);
        $subscription->setStatus(Subscription::ACTIVE);
        $subscription->setProviderSubscriptionId($providerId);
        $subscription->setRenewalDate($renewalDate);
        $subscription->setExpiryDate($expiryDate);
        $this->subscriptionService->save($subscription);
    }

    public function renew(string $provider, ?string $providerSubscriptionId, string $event, DateTimeInterface $nextRenewalDate, DateTimeInterface $expiryDate, array $data): void
    {
        $subscription = $this->subscriptionService->findOneByProviderAndSubscriptionId($provider, $providerSubscriptionId);

        if ($subscription === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::NO_SUBSCRIPTION_FOUND => $data]);
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setRenewalDate($nextRenewalDate);
        $subscription->setExpiryDate($expiryDate);
        $this->subscriptionService->save($subscription);
    }

    public function cancel(string $provider, ?string $providerSubscriptionId, string $event, array $data): void
    {
        $subscription = $this->subscriptionService->findOneByProviderAndSubscriptionId($provider, $providerSubscriptionId);

        if ($subscription === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::NO_SUBSCRIPTION_FOUND => $data]);
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setStatus(Subscription::CANCELLED);
        $subscription->setRenewalDate(null);
        $this->subscriptionService->save($subscription);
    }

    public function failRenewal(string $provider, ?string $providerSubscriptionId, string $event, ?DateTimeInterface $nextRetryDate, array $data): void
    {
        $subscription = $this->subscriptionService->findOneByProviderAndSubscriptionId($provider, $providerSubscriptionId);

        if ($subscription === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::NO_SUBSCRIPTION_FOUND => $data]);
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setStatus(Subscription::RENEWAL_FAILURE);

        if ($nextRetryDate !== null) {
            $subscription->setRenewalDate($nextRetryDate);
        }

        $this->subscriptionService->save($subscription);
    }

    public function chargeback(string $provider, ?string $providerSubscriptionId, string $event, array $data)
    {
        $subscription = $this->subscriptionService->findOneByProviderAndSubscriptionId($provider, $providerSubscriptionId);

        if ($subscription === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::NO_SUBSCRIPTION_FOUND => $data]);
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setStatus(Subscription::CHARGEBACK);
        $subscription->setRenewalDate(null);
        $subscription->setExpiryDate(new DateTimeImmutable());
        $this->subscriptionService->save($subscription);
    }

    public function refund(string $provider, ?string $providerSubscriptionId, string $event, array $data)
    {
        $subscription = $this->subscriptionService->findOneByProviderAndSubscriptionId($provider, $providerSubscriptionId);

        if ($subscription === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::NO_SUBSCRIPTION_FOUND => $data]);
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setStatus(Subscription::REFUND);
        $subscription->setRenewalDate(null);
        $subscription->setExpiryDate(new DateTimeImmutable());
        $this->subscriptionService->save($subscription);
    }

    public function changeBillingDate(string $provider, ?string $providerSubscriptionId, string $event, DateTimeInterface $nextBillingDate, array $data): void
    {
        $subscription = $this->subscriptionService->findOneByProviderAndSubscriptionId($provider, $providerSubscriptionId);

        if ($subscription === null) {
            $this->eventService->save(null, Event::SUBSCRIPTION_ERROR, [self::NO_SUBSCRIPTION_FOUND => $data]);
            return;
        }

        $this->eventService->save($subscription->getUser()->getId(), $event, $data);
        $subscription->setRenewalDate($nextBillingDate);
        $this->subscriptionService->save($subscription);
    }
}
