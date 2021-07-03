<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use DateInterval;
use DateTime;
use DatingLibre\AppBundle\Repository\UserArchiveRepository;
use DatingLibre\AppBundle\Repository\UserSettingRepository;
use DatingLibre\AppBundle\Service\UserArchiveService;
use DatingLibre\AppBundle\Service\UserService;
use Webmozart\Assert\Assert;

class AccountContext extends RawMinkContext
{
    private UserService $userService;
    private UserArchiveService $userArchiveService;
    private UserArchiveRepository $userArchiveRepository;
    private UserSettingRepository $userSettingRepository;

    public function __construct(UserService $userService,
                                UserSettingRepository $userSettingRepository,
                                UserArchiveService $userArchiveService,
                                UserArchiveRepository $userArchiveRepository)
    {
        $this->userService = $userService;
        $this->userSettingRepository = $userSettingRepository;
        $this->userArchiveService = $userArchiveService;
        $this->userArchiveRepository = $userArchiveRepository;
    }

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->userArchiveRepository->deleteAll();
    }

    /**
     * @Then the account setting :settingName for :email should be :value
     */
    public function theAccountSettingShouldBe(string $settingName, string $email, string $value)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $setting = $this->userSettingRepository->findOneBy(['user' => $user, $settingName => $value === "true"]);
        Assert::notNull($setting);
    }

    /**
     * @Then there should be a user archive for :email
     */
    public function aUserArchiveExistsFor(string $email): void
    {
        $userArchive = $this->userArchiveService->findByEmail($email);
        Assert::notNull($userArchive);
        Assert::eq($userArchive->getArchive()['profile']['username'], 'newuser');
    }

    /**
     * @Given I create an old archive for :email
     */
    public function iCreateAnOldArchiveFor(string $email): void
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $this->userArchiveService->saveArchive($user->getId());
        $this->userArchiveRepository->updateCreatedAt($email, (new DateTime())->sub(new DateInterval('P61D')));
    }

    /**
     * @When I run the purge user archives command
     */
    public function purgeUserArchives(): void
    {
        $this->userArchiveService->deleteOlderThanDays(60);
    }

    /**
     * @Then there should not be an archive for :email
     */
    public function thereShouldNotBeAnArchiveFor(string $email)
    {
        $userArchive = $this->userArchiveService->findByEmail($email);
        Assert::null($userArchive);
    }
}
