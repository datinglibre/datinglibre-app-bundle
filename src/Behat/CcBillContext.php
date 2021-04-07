<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use Behat\Behat\Context\Context;
use DatingLibre\AppBundle\Repository\EventRepository;
use DatingLibre\AppBundle\Repository\SubscriptionRepository;
use DatingLibre\AppBundle\Service\ccBillService;
use DatingLibre\AppBundle\Service\UserService;
use DatingLibre\CcBill\Event\BillingDateChangeEvent;
use DatingLibre\CcBill\Event\CancellationEvent;
use DatingLibre\CcBill\Event\CcBillEventConstants;
use DatingLibre\CcBill\Event\ChargebackEvent;
use DatingLibre\CcBill\Event\ErrorEvent;
use DatingLibre\CcBill\Event\NewSaleFailureEvent;
use DatingLibre\CcBill\Event\NewSaleSuccessEvent;
use DatingLibre\CcBill\Event\RefundEvent;
use DatingLibre\CcBill\Event\RenewalFailureEvent;
use DatingLibre\CcBill\Event\RenewalSuccessEvent;
use Webmozart\Assert\Assert;

class CcBillContext implements Context
{
    private UserService $userService;
    private SubscriptionRepository $subscriptionRepository;
    private ccBillService $ccBillService;
    private EventRepository $eventRepository;

    protected const TEST_SUBSCRIPTION_ID = '1000000000';
    protected const TEST_TRANSACTION_ID = '0912191101000000159';
    protected const TEST_TIMESTAMP = '2012-08-05 15:18:17';
    protected const TEST_USERNAME = 'username1';
    protected const TEST_PASSWORD = 'mYPaSSw0rD';
    protected const TEST_CLIENT_ACCOUNT_NO = '900100';
    protected const TEST_CLIENT_SUB_ACCOUNT_NO = '0000';
    protected const TEST_EMAIL = 'user@randomurl.com';
    protected const TEST_RENEWAL_DATE = '2012-07-20';
    protected const TEST_NEXT_RENEWAL_DATE = '2012-08-20';
    protected const TEST_FIRSTNAME = 'John';
    protected const TEST_LASTNAME = 'Doe';
    protected const TEST_ADDRESS_1 = '123 Main Street';
    protected const TEST_CITY = 'Anytown';
    protected const TEST_STATE = 'AZ';
    protected const TEST_COUNTRY = 'US';
    protected const TEST_POSTAL_CODE = '50115';
    protected const TEST_PHONE_NUMBER = '	(515) 555-1212';
    protected const TEST_IP_ADDRESS = '192.168.27.4';
    protected const TEST_RESERVATION_ID = '0109072310330002423';
    protected const TEST_FORM_NAME = '13cc';
    protected const TEST_FLEX_ID = 'cb617dcc-8467-49ab-b3a7-735ce1d60ad9';
    protected const TEST_PRICE_DESCRIPTION = '10.00(USD) for 10 days (trial) then 10.00(USD) recurring every 30 days';
    protected const TEST_RECURRING_PRICE_DESCRIPTION = '22.22(USD) recurring every 30 days';
    protected const TEST_BILLED_INITIAL_PRICE = '4.95';
    protected const TEST_BILLED_RECURRING_PRICE = '19.95';
    protected const TEST_BILLED_CURRENCY_CODE = '879';
    protected const TEST_SUBSCRIPTION_INITIAL_PRICE = '4.99';
    protected const TEST_SUBSCRIPTION_RECURRING_PRICE = '5.99';
    protected const TEST_SUBSCRIPTION_CURRENCY_CODE = '978';
    protected const TEST_ACCOUNTING_INITIAL_PRICE = '3.99';
    protected const TEST_ACCOUNTING_RECURRING_PRICE = '9.99';
    protected const TEST_ACCOUNTING_CURRENCY_CODE = '930';
    protected const TEST_INITIAL_PERIOD = '7';
    protected const TEST_RECURRING_PERIOD = '30';
    protected const TEST_REBILLS = '12';
    protected const TEST_SUBSCRIPTION_TYPE_ID = '0000060748';
    protected const TEST_DYNAMIC_PRICING_VALIDATION_DIGEST = 's4f5198jgd21a4pk1p2s7sd23lm58937';
    protected const TEST_PAYMENT_TYPE = 'CREDIT';
    protected const TEST_CARD_TYPE = 'VISA';
    protected const TEST_BIN = '510510';
    protected const TEST_PRE_PAID = '0';
    protected const TEST_LAST_4 = '5100';
    protected const TEST_EXPIRY_DATE = '0217';
    protected const TEST_AVS_RESPONSE = 'Y';
    protected const TEST_CVV2_RESPONSE = 'M';
    protected const TEST_AFFILIATE_SYSTEM = 'LTS';
    protected const TEST_REFERRING_URL = 'http://www.referringurl.biz';
    protected const TEST_LIFETIME_SUBSCRIPTION = '1';
    protected const TEST_LIFETIME_PRICE = '40.25';
    protected const TEST_PAYMENT_ACCOUNT = '57bc7327b5d721d7d20b240c0357e6ed';
    protected const TEST_3_D_SECURE = 'AUTH_SUCCESS';
    protected const TEST_FAILURE_REASON = 'Invalid Input.';
    protected const TEST_FAILURE_CODE = 'BE-140';
    protected const TEST_SOURCE = 'FORM';
    protected const TEST_CANCELLATION_REASON = 'Customer Refunded';
    protected const TEST_NEXT_RETRY_DATE = '2012-08-21';
    protected const TEST_REASON = 'Customer Refunded';

    public function __construct(
        UserService $userService,
        EventRepository $eventRepository,
        SubscriptionRepository $subscriptionRepository,
        CcBillService $ccBillService
    ) {
        $this->userService = $userService;
        $this->eventRepository = $eventRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->ccBillService = $ccBillService;
    }

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->eventRepository->deleteAll();
        $this->subscriptionRepository->deleteAll();
    }

    /**
     * @Given the user :email has bought a new CcBill subscription that has ID :providerSubscriptionId
     */
    public function buyUserCcBillSubscription(string $email, string $providerSubscriptionId): void
    {
        $user = $this->userService->findByEmail($email);

        $this->ccBillService->processEvent(NewSaleSuccessEvent::fromArray($this->getSalePayload(
            [
                CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId,
                CcBillEventConstants::CUSTOM_1 => $user->getId()->toRfc4122()
            ]
        )));
    }

    /**
     * @Given the user :email has failed to buy a new CcBill subscription
     */
    public function failToBuyANewCcBillSubscription(string $email): void
    {
        $user = $this->userService->findByEmail($email);

        $this->ccBillService->processEvent(NewSaleFailureEvent::fromArray($this->getSalePayload(
            [
                CcBillEventConstants::FAILURE_REASON => 'failed',
                CcBillEventConstants::FAILURE_CODE => 'abc',
                CcBillEventConstants::CUSTOM_1 => $user->getId()->toRfc4122()
            ]
        )));
    }

    /**
     * @Given an error event has been raised processing CcBill events
     */
    public function raiseErrorEvent(): void
    {
        $this->ccBillService->processEvent(new ErrorEvent(123, '{"subscriptionId": "123"}'));
    }

    /**
     * @Then a new :eventName event should be created for :email
     */
    public function aNewEventExistsForUser(string $eventName, string $email): void
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $event = $this->eventRepository->findOneBy(['name' => $eventName, 'user' => $user]);
        Assert::notNull($event);
    }

    /**
     * @Given a new sale success event without a user ID
     */
    public function aNewSaleSuccessEventWithoutAUserId(): void
    {
        $this->ccBillService->processEvent(NewSaleSuccessEvent::fromArray($this->getSalePayload(
            [CcBillEventConstants::SUBSCRIPTION_ID => self::TEST_SUBSCRIPTION_ID]
        )));
    }

    /**
     * @Then a new :eventName event with a data payload should be created
     */
    public function aNewEventShouldBeCreated(string $eventName): void
    {
        $event = $this->eventRepository->findOneBy(['name' => $eventName]);
        Assert::notNull($event);
        Assert::notEmpty($event->getData());
    }

    /**
     * @Given there has been a rebill for subscription :remoteId with next renewal date :nextRenewalDate
     */
    public function theUserHasBeenRebilledForTheirSubscriptionWithRenewalDate(
        string $providerSubscriptionId,
        string $nextRenewalDate
    ): void {
        // next renewal date: next billing date for recurring subscriptions
        $this->ccBillService->processEvent(RenewalSuccessEvent::fromArray($this->getRenewalPayload(
            [
                CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId,
                CcBillEventConstants::NEXT_RENEWAL_DATE => $nextRenewalDate
            ]
        )));
    }

    /**
     * @Given there has been a failed rebill for subscription :providerSubscriptionId with next retry date :nextRetryDate
     */
    public function thereHasBeenAFailedRebillForSubscription(string $providerSubscriptionId, string $nextRetryDate): void
    {
        $this->ccBillService->processEvent(RenewalFailureEvent::fromArray($this->getRenewalFailurePayload(
            [
                CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId,
                CcBillEventConstants::NEXT_RETRY_DATE => $nextRetryDate
            ]
        )));
    }

    /**
     * @Given there has been a cancellation for :providerSubscriptionId
     */
    public function thereHasBeenACancellationFor(string $providerSubscriptionId): void
    {
        $this->ccBillService->processEvent(CancellationEvent::fromArray($this->getCancellationPayload(
            [CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId]
        )));
    }

    /**
     * @Given there has been a chargeback for :providerSubscriptionId
     */
    public function thereHasBeenAChargebackFor(string $providerSubscriptionId)
    {
        $this->ccBillService->processEvent(ChargebackEvent::fromArray($this->getChargebackPayload(
            [CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId]
        )));
    }

    /**
     * @Given there has been a refund for :providerSubscriptionId
     */
    public function thereHasBeenARefundFor(string $providerSubscriptionId)
    {
        $this->ccBillService->processEvent(RefundEvent::fromArray($this->getRefundPayload(
            [CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId]
        )));
    }

    /**
     * @Given there has been a billing date change for :providerSubscriptionId to :date
     */
    public function thereHasBeenABillingDateChange(string $providerSubscriptionId, string $date)
    {
        $this->ccBillService->processEvent(BillingDateChangeEvent::fromArray($this->getBillingDateChangePayload(
            [
                CcBillEventConstants::SUBSCRIPTION_ID => $providerSubscriptionId,
                CcBillEventConstants::NEXT_RENEWAL_DATE => $date
            ]
        )));
    }

    private function getBillingDateChangePayload(array $merge)
    {
        return array_merge(
            [
                CcBillEventConstants::TRANSACTION_ID => self::TEST_TRANSACTION_ID,
                CcBillEventConstants::CLIENT_ACCOUNT_NO => self::TEST_CLIENT_ACCOUNT_NO,
                CcBillEventConstants::CLIENT_SUB_ACCOUNT_NO => self::TEST_CLIENT_SUB_ACCOUNT_NO,
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP
            ],
            $merge
        );
    }

    public function getRenewalPayload(array $merge): array
    {
        // renewal date: The transaction date of the rebill.
        return array_merge(
            [
                CcBillEventConstants::TRANSACTION_ID => self::TEST_TRANSACTION_ID,
                CcBillEventConstants::CLIENT_ACCOUNT_NO => self::TEST_CLIENT_ACCOUNT_NO,
                CcBillEventConstants::CLIENT_SUB_ACCOUNT_NO => self::TEST_CLIENT_SUB_ACCOUNT_NO,
                CcBillEventConstants::RENEWAL_DATE => self::TEST_RENEWAL_DATE,
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP
            ],
            $merge
        );
    }

    public function getRenewalFailurePayload(array $merge): array
    {
        return array_merge(
            [
                CcBillEventConstants::TRANSACTION_ID => self::TEST_TRANSACTION_ID,
                CcBillEventConstants::CLIENT_ACCOUNT_NO => self::TEST_CLIENT_ACCOUNT_NO,
                CcBillEventConstants::CLIENT_SUB_ACCOUNT_NO => self::TEST_CLIENT_SUB_ACCOUNT_NO,
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP,
                CcBillEventConstants::NEXT_RETRY_DATE => self::TEST_NEXT_RETRY_DATE,
                CcBillEventConstants::FAILURE_CODE => self::TEST_FAILURE_CODE,
                CcBillEventConstants::FAILURE_REASON => self::TEST_FAILURE_REASON,
                CcBillEventConstants::RENEWAL_DATE => self::TEST_NEXT_RENEWAL_DATE,
                CcBillEventConstants::CARD_TYPE => self::TEST_CARD_TYPE,
                CcBillEventConstants::PAYMENT_TYPE => self::TEST_PAYMENT_TYPE
            ],
            $merge
        );
    }

    private function getChargebackPayload(array $merge): array
    {
        return array_merge(
            [
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP,
                CcBillEventConstants::TRANSACTION_ID => self::TEST_TRANSACTION_ID,
                CcBillEventConstants::CARD_TYPE => self::TEST_CARD_TYPE,
                CcBillEventConstants::PAYMENT_TYPE => self::TEST_PAYMENT_TYPE,
                CcBillEventConstants::LAST_FOUR => self::TEST_LAST_4
            ],
            $merge
        );
    }

    private function getRefundPayload(array $merge): array
    {
        return array_merge(
            [
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP,
                CcBillEventConstants::TRANSACTION_ID => self::TEST_TRANSACTION_ID,
                CcBillEventConstants::REASON => self::TEST_REASON,
                CcBillEventConstants::CARD_TYPE => self::TEST_CARD_TYPE,
                CcBillEventConstants::PAYMENT_TYPE => self::TEST_PAYMENT_TYPE,
                CcBillEventConstants::LAST_FOUR => self::TEST_LAST_4
            ],
            $merge
        );
    }

    private function getCancellationPayload(array $merge): array
    {
        return array_merge(
            [
                CcBillEventConstants::REASON => self::TEST_CANCELLATION_REASON,
                CcBillEventConstants::SOURCE => self::TEST_SOURCE,
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP
            ],
            $merge
        );
    }

    public function getSalePayload(array $merge): array
    {
        return array_merge(
            [
                CcBillEventConstants::TRANSACTION_ID => self::TEST_TRANSACTION_ID,
                CcBillEventConstants::CLIENT_ACCOUNT_NO => self::TEST_CLIENT_ACCOUNT_NO,
                CcBillEventConstants::CLIENT_SUB_ACCOUNT_NO => self::TEST_CLIENT_SUB_ACCOUNT_NO,
                CcBillEventConstants::TIMESTAMP => self::TEST_TIMESTAMP,
                CcBillEventConstants::FIRSTNAME => self::TEST_FIRSTNAME,
                CcBillEventConstants::LASTNAME => self::TEST_LASTNAME,
                CcBillEventConstants::ADDRESS1 => self::TEST_ADDRESS_1,
                CcBillEventConstants::CITY => self::TEST_CITY,
                CcBillEventConstants::STATE => self::TEST_STATE,
                CcBillEventConstants::COUNTRY => self::TEST_COUNTRY,
                CcBillEventConstants::POSTAL_CODE => self::TEST_POSTAL_CODE,
                CcBillEventConstants::EMAIL => self::TEST_EMAIL,
                CcBillEventConstants::PHONE_NUMBER => self::TEST_PHONE_NUMBER,
                CcBillEventConstants::IP_ADDRESS => self::TEST_IP_ADDRESS,
                CcBillEventConstants::RESERVATION_ID => self::TEST_RESERVATION_ID,
                CcBillEventConstants::USERNAME => self::TEST_USERNAME,
                CcBillEventConstants::PASSWORD => self::TEST_PASSWORD,
                CcBillEventConstants::FORM_NAME => self::TEST_FORM_NAME,
                CcBillEventConstants::FLEX_ID => self::TEST_FLEX_ID,
                CcBillEventConstants::PRICE_DESCRIPTION => self::TEST_PRICE_DESCRIPTION,
                CcBillEventConstants::RECURRING_PRICE_DESCRIPTION => self::TEST_RECURRING_PRICE_DESCRIPTION,
                CcBillEventConstants::BILLED_INITIAL_PRICE => self::TEST_BILLED_INITIAL_PRICE,
                CcBillEventConstants::BILLED_RECURRING_PRICE => self::TEST_BILLED_RECURRING_PRICE,
                CcBillEventConstants::BILLED_CURRENCY_CODE => self::TEST_BILLED_CURRENCY_CODE,
                CcBillEventConstants::SUBSCRIPTION_INITIAL_PRICE => self::TEST_SUBSCRIPTION_INITIAL_PRICE,
                CcBillEventConstants::SUBSCRIPTION_RECURRING_PRICE => self::TEST_SUBSCRIPTION_RECURRING_PRICE,
                CcBillEventConstants::SUBSCRIPTION_CURRENCY_CODE => self::TEST_SUBSCRIPTION_CURRENCY_CODE,
                CcBillEventConstants::ACCOUNTING_INITIAL_PRICE => self::TEST_ACCOUNTING_INITIAL_PRICE,
                CcBillEventConstants::ACCOUNTING_RECURRING_PRICE => self::TEST_ACCOUNTING_RECURRING_PRICE,
                CcBillEventConstants::ACCOUNTING_CURRENCY_CODE => self::TEST_ACCOUNTING_CURRENCY_CODE,
                CcBillEventConstants::INITIAL_PERIOD => self::TEST_INITIAL_PERIOD,
                CcBillEventConstants::RECURRING_PERIOD => self::TEST_RECURRING_PERIOD,
                CcBillEventConstants::REBILLS => self::TEST_REBILLS,
                CcBillEventConstants::NEXT_RENEWAL_DATE => self::TEST_NEXT_RENEWAL_DATE,
                CcBillEventConstants::SUBSCRIPTION_TYPE_ID => self::TEST_SUBSCRIPTION_TYPE_ID,
                CcBillEventConstants::DYNAMIC_PRICING_VALIDATION_DIGEST => self::TEST_DYNAMIC_PRICING_VALIDATION_DIGEST,
                CcBillEventConstants::PAYMENT_TYPE => self::TEST_PAYMENT_TYPE,
                CcBillEventConstants::CARD_TYPE => self::TEST_CARD_TYPE,
                CcBillEventConstants::BIN => self::TEST_BIN,
                CcBillEventConstants::PRE_PAID => self::TEST_PRE_PAID,
                CcBillEventConstants::LAST_FOUR => self::TEST_LAST_4,
                CcBillEventConstants::EXP_DATE => self::TEST_EXPIRY_DATE,
                CcBillEventConstants::AVS_RESPONSE => self::TEST_AVS_RESPONSE,
                CcBillEventConstants::CVV2_RESPONSE => self::TEST_CVV2_RESPONSE,
                CcBillEventConstants::AFFILIATE_SYSTEM => self::TEST_AFFILIATE_SYSTEM,
                CcBillEventConstants::REFERRING_URL => self::TEST_REFERRING_URL,
                CcBillEventConstants::LIFETIME_SUBSCRIPTION => self::TEST_LIFETIME_SUBSCRIPTION,
                CcBillEventConstants::LIFETIME_PRICE => self::TEST_LIFETIME_PRICE,
                CcBillEventConstants::PAYMENT_ACCOUNT => self::TEST_PAYMENT_ACCOUNT,
                CcBillEventConstants::THREE_D_SECURE => self::TEST_3_D_SECURE
            ],
            $merge
        );
    }
}
