<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat\Page;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Webmozart\Assert\Assert;

class BlockPage extends SymfonyPage
{
    public function getRouteName(): string
    {
        return 'user_block';
    }

    public function assertContains(string $value): void
    {
        Assert::contains($this->getSession()->getPage()->getContent(), $value);
    }
}
