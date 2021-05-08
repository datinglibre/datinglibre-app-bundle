<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use DatingLibre\AppBundle\Behat\Util\EmailUtil;
use DatingLibre\AppBundle\Behat\Page\RegistrationPage;
use Behat\Behat\Context\Context;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final class RegistrationContext implements Context
{
    private RegistrationPage $registrationPage;
    private string $signupEmail;
    private string $alreadyExistsEmail;

    public function __construct(
        RegistrationPage $registrationPage
    ) {
        $this->registrationPage = $registrationPage;
    }

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->signupEmail = '';
        $this->alreadyExistsEmail = '';
        EmailUtil::deleteAll();
    }

    /**
     * @When I fill in my registration details correctly with email :email
     */
    public function iFillInMyRegistrationDetailsCorrectlyWithEmail(string $email): void
    {
        $this->email = $email;
        Assert::true($this->registrationPage->isOpen());
        $this->registrationPage->fillInDetails($email);
    }

    /**
     * @Then I should receive a confirmation email to :email
     */
    public function iShouldReceiveAnEmailToMyAddress(string $email)
    {
        $signupEmail = EmailUtil::getEmail($email);
        $this->signupEmail = $signupEmail->getBody();
        Assert::eq($signupEmail->getSubject(), 'Confirm your account');
        Assert::contains($this->signupEmail, 'Your email address has been used to create an account');
    }

    /**
     * @Given I click the confirmation link and see :message
     */
    public function iClickTheConfirmationLinkAndSee(string $message)
    {
        $confirmResponse = $this->clickLink($this->getFirstLink($this->signupEmail));
        Assert::contains($confirmResponse->getContent(), $message);
    }

    /**
     * @Given I click the confirmation link with the incorrect secret and see :message
     */
    public function iClickTheConfirmationLinkWithTheIncorrectSecretAndSee(string $message)
    {
        // append a digit to invalid the secret before the query string,
        $confirmLink = str_replace(
            '?',
            '0?',
            $this->getFirstLink($this->signupEmail)
        );

        $confirmResponse = $this->clickLink($confirmLink);

        Assert::contains($confirmResponse->getContent(), $message);
        Assert::eq($confirmResponse->getInfo()['response_headers'][0], 'HTTP/1.1 302 Found');
    }

    /**
     * @Given I should receive an already exists email to :email
     */
    public function iShouldReceiveAnAlreadyExistsEmailTo(string $email)
    {
        $alreadyExistsEmail = EmailUtil::getEmail($email);
        $this->alreadyExistsEmail = $alreadyExistsEmail->getBody();
        Assert::eq($alreadyExistsEmail->getSubject(), 'An account for your email address already exists');
        Assert::contains($this->alreadyExistsEmail, 'If you have forgotten your password please use the');
        Assert::contains($this->alreadyExistsEmail, 'password reset form');
    }

    /**
     * @Then I can reset my password using the link
     */
    public function iCanResetMyPasswordUsingTheLink()
    {
        $response = $this->clickLink($this->getFirstLink($this->alreadyExistsEmail));
        Assert::contains($response->getContent(), 'Reset password');
    }

    private function getFirstLink($email): string
    {
        $crawler = new Crawler($email);
        return $crawler->filterXPath('//a/@href')->getNode(0)->nodeValue;
    }

    private function clickLink($link): ResponseInterface
    {
        $response = HttpClient::create()->request('GET', $link);
        Assert::eq($response->getStatusCode(), 200);

        return $response;
    }
}
