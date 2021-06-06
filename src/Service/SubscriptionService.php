<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Subscription;
use DatingLibre\AppBundle\Repository\SubscriptionRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Uid\Uuid;

class SubscriptionService
{
    use ContainerAwareTrait;

    private const UNRECOGNIZED_SUBSCRIPTION_PROVIDER = 'Unrecognized [%s] subscription provider [%s]';
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function findByUserId(Uuid $id): array
    {
        return $this->subscriptionRepository->findBy(['user' => $id]);
    }

    /**
     * @throws Exception
     */
    public function cancel(Uuid $userId): void
    {
        $subscription = $this->subscriptionRepository->findOneBy(
            [
                'user' => $userId,
                Subscription::STATUS => Subscription::ACTIVE,
            ]
        );

        if ($subscription === null) {
            return;
        }

        $this->cancelProviderSubscription($subscription);
        $subscription->setStatus(Subscription::CANCELLED);
    }

    public function findOneByProviderAndSubscriptionId(string $provider, ?string $providerSubscriptionId): ?Subscription
    {
        return $this->subscriptionRepository->findOneBy([
            Subscription::PROVIDER_SUBSCRIPTION_ID => $providerSubscriptionId,
            Subscription::PROVIDER => $provider
        ]);
    }

    public function save(Subscription $subscription): Subscription
    {
        return $this->subscriptionRepository->save($subscription);
    }

    /**
     * @throws Exception
     */
    private function cancelProviderSubscription(Subscription $subscription): void
    {
        switch ($subscription->getProvider()) {
            case Subscription::CCBILL:
                $this->container->get('DatingLibre\CcBillBundle\Service\CcBillClientService')
                    ->cancelSubscription($subscription->getProviderSubscriptionId());
                break;
            case Subscription::DATINGLIBRE:
                break;
            default:
                throw new Exception(sprintf(
                    self::UNRECOGNIZED_SUBSCRIPTION_PROVIDER,
                    $subscription->getId(),
                    $subscription->getProvider()
                ));
        }
    }
}
