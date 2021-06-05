<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Event;
use DatingLibre\AppBundle\Entity\Subscription;
use DatingLibre\CcBill\Event\BillingDateChangeEvent;
use DatingLibre\CcBill\Event\CancellationEvent;
use DatingLibre\CcBill\Event\CcBillEvent;
use DatingLibre\CcBill\Event\ChargebackEvent;
use DatingLibre\CcBill\Event\ErrorEvent;
use DatingLibre\CcBill\Event\NewSaleFailureEvent;
use DatingLibre\CcBill\Event\NewSaleSuccessEvent;
use DatingLibre\CcBill\Event\RefundEvent;
use DatingLibre\CcBill\Event\RenewalFailureEvent;
use DatingLibre\CcBill\Event\RenewalSuccessEvent;
use Exception;
use Symfony\Component\Uid\Uuid;

class CcBillEventService
{
    private const EMAIL = 'email';
    private const SUBSCRIPTION_ID = 'subscriptionId';
    private const TIMESTAMP = 'timestamp';
    private const CODE = 'code';
    private const MESSAGE = 'message';
    private const TRANSACTION_ID = 'transactionId';
    private const DATETIME_FORMAT = 'Y-m-d H:m:s';
    private const DATE_FORMAT = 'Y-m-d';
    private const CARD_TYPE = 'cardType';
    private const REASON = 'reason';
    private const SOURCE = 'source';
    private const PAYMENT_TYPE = 'paymentType';
    private const NEXT_BILLING_DATE = 'nextBillingDate';

    private EventService $eventService;
    private SubscriptionEventService $subscriptionService;

    public function __construct(
        EventService $eventService,
        SubscriptionEventService $subscriptionEventService
    ) {
        $this->eventService = $eventService;
        $this->subscriptionService = $subscriptionEventService;
    }

    /**
     * @throws Exception
     */
    public function processEvent(CcBillEvent $event): void
    {
        if ($event instanceof NewSaleSuccessEvent) {
            $this->newSaleSuccess($event);
        } elseif ($event instanceof NewSaleFailureEvent) {
            $this->newSaleFailure($event);
        } elseif ($event instanceof RenewalSuccessEvent) {
            $this->renewalSuccess($event);
        } elseif ($event instanceof RenewalFailureEvent) {
            $this->renewalFailure($event);
        } elseif ($event instanceof CancellationEvent) {
            $this->cancellation($event);
        } elseif ($event instanceof ChargebackEvent) {
            $this->chargeback($event);
        } elseif ($event instanceof RefundEvent) {
            $this->refund($event);
        } elseif ($event instanceof BillingDateChangeEvent) {
            $this->billingDateChange($event);
        } elseif ($event instanceof ErrorEvent) {
            $this->error($event);
        }
    }

    private function renewalSuccess(RenewalSuccessEvent $event): void
    {
        $this->subscriptionService->renew(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_RENEWAL,
            $event->getNextRenewalDate(),
            $event->getNextRenewalDate(),
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType(),
            ]
        );
    }

    private function renewalFailure(RenewalFailureEvent $event): void
    {
        $this->subscriptionService->failRenewal(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_RENEWAL_FAILURE,
            $event->getNextRetryDate(),
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType()
            ]
        );
    }

    /**
     * If the subscription payment successful, but we don't have at
     * least a valid user UUID to associate it to, it should fail.
     *
     * @throws Exception
     */
    private function newSaleSuccess(NewSaleSuccessEvent $event): void
    {
        $this->subscriptionService->create(
            $this->requireUserId($event->getCustom1()),
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_NEW_SALE,
            $event->getNextRenewalDate(),
            $event->getNextRenewalDate(),
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType()
            ]
        );
    }

    private function newSaleFailure(NewSaleFailureEvent $event): void
    {
        $this->eventService->save(
            $this->parseUserId($event->getCustom1()),
            Event::CCBILL_NEW_SALE_FAILURE,
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::EMAIL => $event->getEmail(),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType()
            ]
        );
    }

    private function cancellation(CancellationEvent $event)
    {
        $this->subscriptionService->cancel(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_CANCELLATION,
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::REASON => $event->getReason(),
                self::SOURCE => $event->getSource(),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId()
            ]
        );
    }

    private function chargeback(ChargebackEvent $event)
    {
        $this->subscriptionService->chargeback(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_CHARGEBACK,
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::REASON => $event->getReason(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType()
            ]
        );
    }

    private function refund(RefundEvent $event): void
    {
        $this->subscriptionService->refund(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_REFUND,
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::REASON => $event->getReason(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType()
            ]
        );
    }

    private function billingDateChange(BillingDateChangeEvent $event)
    {
        $this->subscriptionService->changeBillingDate(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCILL_BILLING_DATE_CHANGE,
            $event->getNextRenewalDate(),
            [
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::NEXT_BILLING_DATE => $event->getNextRenewalDate()->format(self::DATE_FORMAT)
            ]
        );
    }

    public function error(ErrorEvent $event): void
    {
        $this->eventService->save(
            null,
            Event::SUBSCRIPTION_ERROR,
            [
                self::CODE => $event->getErrorCode(),
                self::MESSAGE => $event->getErrorMessage()
            ]
        );
    }

    private function parseUserId(?string $userId): ?Uuid
    {
        return $userId !== null && Uuid::isValid($userId) ? Uuid::fromString($userId) : null;
    }

    /**
     * @throws Exception
     */
    private function requireUserId(?string $userId): Uuid
    {
        if ($userId === null || !Uuid::isValid($userId)) {
            throw new Exception(sprintf('Invalid userId [%s]', $userId));
        }

        return Uuid::fromString($userId);
    }
}
