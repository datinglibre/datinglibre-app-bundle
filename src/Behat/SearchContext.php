<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use DatingLibre\AppBundle\Behat\Util\TableUtil;
use DatingLibre\AppBundle\Entity\Filter;
use DatingLibre\AppBundle\Entity\ProfileProjection;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Repository\FilterRepository;
use DatingLibre\AppBundle\Service\UserInterestFilterService;
use DatingLibre\AppBundle\Service\UserService;
use DatingLibre\AppBundle\Behat\Page\SearchPage;
use Behat\Gherkin\Node\TableNode;
use DatingLibre\AppBundle\Repository\RegionRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

class SearchContext extends RawMinkContext
{
    private UserService $userService;
    private ProfileRepository $profileRepository;
    private RegionRepository $regionRepository;
    private SearchPage $searchPage;
    private FilterRepository $filterRepository;
    private array $profiles;
    private UserInterestFilterService $userInterestFilterService;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        UserService $userService,
        ProfileRepository $profileRepository,
        RegionRepository $regionRepository,
        SearchPage $searchPage,
        FilterRepository $filterRepository,
        UserInterestFilterService $userInterestFilterService,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->userService = $userService;
        $this->profileRepository = $profileRepository;
        $this->searchPage = $searchPage;
        $this->filterRepository = $filterRepository;
        $this->regionRepository = $regionRepository;
        $this->userInterestFilterService = $userInterestFilterService;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @When the user :email searches for matches
     */
    public function theUserSearchesForMatches(string $email)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $profile = $this->profileRepository->find($user->getId());
        Assert::notNull($profile);

        $city = $profile->getCity();

        $filter = $this->filterRepository->findOneBy(['user' => $user->getId()]);
        Assert::notNull($filter);

        $this->profiles = $this->profileRepository->findByLocation(
            $user->getId(),
            $city->getLatitude(),
            $city->getLongitude(),
            $filter->getDistance(),
            empty($filter->getRegion()) ? null : $filter->getRegion()->getId(),
            $filter->getMinAge(),
            $filter->getMaxAge(),
            0,
            0,
            10
        );
    }

    /**
     * @Then the user :email matches
     */
    public function theUserMatches(string $email)
    {
        Assert::true($this->containsProfile($this->userService->findByEmail($email)));
    }

    /**
     * @Then the user :email does not match
     */
    public function theUserDoesNotMatch(string $email)
    {
        Assert::false($this->containsProfile($this->userService->findByEmail($email)));
    }

    /**
     * @Then the following users match:
     */
    public function theFollowingUsersMatch(TableNode $table)
    {
        $users = [];
        foreach ($table as $row) {
            $user = $this->userService->findByEmail(trim($row['email']));
            Assert::notNull($user);
            $users[] = $user;
        }

        $this->containsMatches($users);
    }

    /**
     * @Then I should see the user :email
     */
    public function iShouldSeeTheUser(string $email)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);
        $this->searchPage->assertContains($user->getId()->toRfc4122());
    }

    /**
     * @Given I navigate to the search page
     */
    public function iNavigateToTheSearchPage()
    {
        $this->searchPage->open();
        Assert::true($this->searchPage->isOpen());
    }

    /**
     * @Then I should see that no profiles match
     */
    public function iShouldSeeThatNoProfilesMatch()
    {
        $this->searchPage->assertContains('No results, please try changing your search criteria');
    }

    /**
     * @Given the following filters exist:
     */
    public function theFollowingFiltersExist(TableNode $table)
    {
        foreach ($table as $row) {
            $user = $this->userService->findByEmail($row['email']);
            Assert::notNull($user);

            $filter = new Filter();
            $filter->setUser($user);

            if (array_key_exists('region', $row)) {
                $region = $this->regionRepository->findOneBy(['name' => $row['region']]);
                Assert::notNull($region);
                $filter->setRegion($region);
            } else {
                $filter->setRegion(null);
            }

            if (array_key_exists('distance', $row)) {
                $filter->setDistance((int) $row['distance']);
            } else {
                $filter->setDistance(null);
            }

            $filter->setMinAge((int) $row['min_age']);
            $filter->setMaxAge((int) $row['max_age']);

            $this->filterRepository->save($filter);
        }
    }

    /**
     * @Given the following interest filters exist:
     */
    public function theFollowingInterestFiltersExist(TableNode $table)
    {
        foreach ($table as $row) {
            $user = $this->userService->findByEmail($row['email']);
            Assert::notNull($user);

            $interests = TableUtil::parseCsvRow($row['interests']);

            $this->userInterestFilterService->createUserInterestFilterByNames(
                $user,
                $interests
            );
        }
    }

    /**
     * @Given I select the profile :username
     */
    public function iSelectTheProfile(string $username)
    {
        $this->searchPage->selectUsername($username);
    }

    /**
     * @Then the image of :email should not appear
     */
    public function theImageOfShouldNotAppear(string $email)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $profile = $this->getProfile($user);
        Assert::notNull($profile);

        Assert::null($profile->getImageUrl());
        Assert::null($profile->getImageStatus());
        Assert::false($profile->isImagePresent());
    }

    /**
     * @Then the image of :email should appear
     */
    public function theImageOfShouldAppear(string $email)
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $profile = $this->getProfile($user);
        Assert::notNull($profile);

        Assert::notNull($profile->getImageUrl());
        Assert::notNull($profile->getImageStatus());
        Assert::true($profile->isImagePresent());
    }

    /**
     * @Then the profile of :email should be 404
     */
    public function assertProfile404(string $email): void
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $profilePage = $this->urlGenerator->generate('user_profile_view', ['userId' => $user->getId()]);

        $this->getSession()->visit($profilePage);

        Assert::eq($this->getSession()->getStatusCode(), 404);
    }

    /**
     * @Then the message page of :email should be 404
     */
    public function assertMessagePage404(string $email): void
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $messagePage = $this->urlGenerator->generate('user_send_message', ['userId' => $user->getId()]);

        $this->getSession()->visit($messagePage);

        Assert::eq($this->getSession()->getStatusCode(), 404);
    }

    /**
     * @Then the block page of :email should be 404
     */
    public function assertBlockPage404(string $email): void
    {
        $user = $this->userService->findByEmail($email);
        Assert::notNull($user);

        $blockPage = $this->urlGenerator->generate('user_block_create', ['userId' => $user->getId()]);

        $this->getSession()->visit($blockPage);

        Assert::eq($this->getSession()->getStatusCode(), 404);
    }

    private function containsMatches(array $users): void
    {
        Assert::eq(count($this->profiles), count($users), sprintf(
            'Match count different, got %d',
            count($this->profiles)
        ));

        foreach ($users as $user) {
            Assert::true($this->containsProfile($user), $user->getId());
        }
    }

    private function getProfile(User $user): ?ProfileProjection
    {
        foreach ($this->profiles as $profile) {
            if ($user->getId()->toRfc4122() === $profile->getId()) {
                return $profile;
            }
        }

        return null;
    }

    private function containsProfile(User $user): bool
    {
        $found = false;

        foreach ($this->profiles as $profile) {
            if ($user->getId()->toRfc4122() === $profile->getId()) {
                $found = true;
            }
        }

        return $found;
    }
}
