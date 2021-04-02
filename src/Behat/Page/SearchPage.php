<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat\Page;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Webmozart\Assert\Assert;

class SearchPage extends SymfonyPage
{
    public function getRouteName(): string
    {
        return "search_index";
    }

    public function assertContains(string $content): void
    {
        Assert::contains(
            $this->getDriver()->getContent(),
            $content,
            'Missing ' . $content
        );
    }

    public function selectUsername(string $username)
    {
        $usernameLink = $this->getSession()->getPage()->findLink($username);
        Assert::notNull($usernameLink);
        $usernameLink->click();
    }
}
