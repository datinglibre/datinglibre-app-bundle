<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use DatingLibre\AppBundle\Behat\Util\EmailUtil;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Repository\UserRepository;
use DatingLibre\AppBundle\Service\UserService;
use DatingLibre\AppBundle\Behat\Page\LoginPage;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class UserContext extends RawMinkContext
{
    public const DEFAULT_PASSWORD = 'password';
    private UserRepository $userRepository;
    private UserService $userService;
    private LoginPage $loginPage;
    private string $passwordResetEmail;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService,
        LoginPage $loginPage
    ) {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->loginPage = $loginPage;
    }

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->passwordResetEmail = '';
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $this->userService->delete(null, $user->getId());
        }
    }

    /**
     * @When I log in using email :email and password :password
     */
    public function iLogInUsingEmailAndPassword($email, $password)
    {
        $this->loginPage->open();
        $this->loginPage->login($email, $password);
    }

    /**
     * @When I log in using email :email
     */
    public function iLogInUsingEmail($email)
    {
        $this->iLogInUsingEmailAndPassword($email, UserContext::DEFAULT_PASSWORD);
    }

    /**
     * @Given a user with email :email and password :password exists
     */
    public function aUserWithEmailAndPasswordExists(string $email, string $password)
    {
        $this->userService->create($email, $password, true, [User::USER]);
    }

    /**
     * @Given a user with email :email
     */
    public function aUserWithEmail($email)
    {
        $this->aUserWithEmailAndPasswordExists($email, self::DEFAULT_PASSWORD);
    }

    /**
     * @Given I am logged in with :email
     */
    public function amLoggedInWithEmail(string $email)
    {
        $this->iLogInUsingEmail($email);
    }

    /**
     * @Given a moderator exists with email :email
     */
    public function aModeratorExistsWithEmail(string $email)
    {
        $this->userService->create(
            $email,
            self::DEFAULT_PASSWORD,
            true,
            [User::MODERATOR]
        );
    }

    /**
     * @Given an administrator exists with email :email
     */
    public function anAdministratorExistsWithEmail(string $email)
    {
        $this->userService->create(
            $email,
            self::DEFAULT_PASSWORD,
            true,
            [User::ADMIN]
        );
    }

    /**
     * @Then I should receive a password reset email to :email
     */
    public function iShouldReceiveAPasswordResetEmailTo(string $email)
    {
        $emailResponse = EmailUtil::getEmail($email);
        Assert::eq($emailResponse->getSubject(), 'Reset password');
        $this->passwordResetEmail = $emailResponse->getBody();
        Assert::contains($this->passwordResetEmail, 'Click the link below to reset your password. Please ignore this email if you did not request to reset your password');
    }

    /**
     * @Then I click the password reset link I should see :message
     */
    public function iClickThePasswordResetLinkIShouldSee(string $message)
    {
        $passwordResetLink = $this->getPasswordResetLink();

        $this->getSession()->visit($passwordResetLink);
        Assert::contains($this->getSession()->getPage()->getContent(), $message);
    }

    /**
     * @Given I click the password reset link with the incorrect secret I should see :message
     */
    public function iClickThePasswordResetLinkWithTheIncorrectSecretIShouldSee(string $message)
    {
        $passwordResetLink = str_replace('&secret=', '&secret=123', $this->getPasswordResetLink());

        $this->getSession()->visit($passwordResetLink);
        Assert::contains($this->getSession()->getPage()->getContent(), $message);
    }

    /**
     * @When the user :email has deleted their account
     */
    public function theUserHasDeletedTheirAccount(string $email)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $this->userService->delete(null, $user->getId());
    }

    public function getPasswordResetLink(): string
    {
        $crawler = new Crawler($this->passwordResetEmail);
        return $crawler->filterXPath('//a/@href')->getNode(0)->nodeValue;
    }
}
