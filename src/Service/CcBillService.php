<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Event;
use DatingLibre\AppBundle\Entity\Subscription;
use DatingLibre\CcBillEventParser\Event\BillingDateChangeEvent;
use DatingLibre\CcBillEventParser\Event\CancellationEvent;
use DatingLibre\CcBillEventParser\Event\CcBillEvent;
use DatingLibre\CcBillEventParser\Event\ChargebackEvent;
use DatingLibre\CcBillEventParser\Event\ErrorEvent;
use DatingLibre\CcBillEventParser\Event\NewSaleFailureEvent;
use DatingLibre\CcBillEventParser\Event\NewSaleSuccessEvent;
use DatingLibre\CcBillEventParser\Event\RefundEvent;
use DatingLibre\CcBillEventParser\Event\RenewalFailureEvent;
use DatingLibre\CcBillEventParser\Event\RenewalSuccessEvent;
use Symfony\Component\Uid\Uuid;

class CcBillService
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
    private const LAST_4 = 'lastFour';
    private const NEXT_BILLING_DATE = 'nextBillingDate';

    private SubscriptionService $subscriptionService;
    private EventService $eventService;

    public function __construct(
        EventService $eventService,
        SubscriptionService $subscriptionService
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->eventService = $eventService;
    }

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

    public function renewalSuccess(RenewalSuccessEvent $event): void
    {
        $this->subscriptionService->renew(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_RENEWAL,
            $event->getNextRenewalDate(),
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType(),
                self::LAST_4 => $event->getLast4()
            ]
        );
    }

    public function renewalFailure(RenewalFailureEvent $event): void
    {
        $this->subscriptionService->failRenewal(
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_RENEWAL_FAILURE,
            $event->getNextRetryDate(),
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::CARD_TYPE => $event->getCardType(),
                self::PAYMENT_TYPE => $event->getPaymentType()
            ]
        );
    }

    public function newSaleSuccess(NewSaleSuccessEvent $event): void
    {
        $this->subscriptionService->create(
            $this->parseUserId($event->getCustom1()),
            Subscription::CCBILL,
            $event->getSubscriptionId(),
            Event::CCBILL_NEW_SALE,
            $event->getNextRenewalDate(),
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::SUBSCRIPTION_ID => $event->getSubscriptionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType(),
                self::LAST_4 => $event->getLast4()
            ]
        );
    }

    public function newSaleFailure(NewSaleFailureEvent $event): void
    {
        $this->eventService->save(
            $this->parseUserId($event->getCustom1()),
            Event::CCBILL_NEW_SALE_FAILURE,
            [
                self::TIMESTAMP => $event->getTimestamp()->format(self::DATETIME_FORMAT),
                self::EMAIL => $event->getEmail(),
                self::TRANSACTION_ID => $event->getTransactionId(),
                self::PAYMENT_TYPE => $event->getPaymentType(),
                self::CARD_TYPE => $event->getCardType(),
                self::LAST_4 => $event->getLast4()
            ]
        );
    }

    public function cancellation(CancellationEvent $event)
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

    public function chargeback(ChargebackEvent $event)
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
                self::CARD_TYPE => $event->getCardType(),
                self::LAST_4 => $event->getLast4()
            ]
        );
    }

    public function refund(RefundEvent $event): void
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
                self::CARD_TYPE => $event->getCardType(),
                self::LAST_4 => $event->getLast4()
            ]
        );
    }

    public function billingDateChange(BillingDateChangeEvent $event)
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
}
