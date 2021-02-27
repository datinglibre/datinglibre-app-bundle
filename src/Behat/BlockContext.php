<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use DatingLibre\AppBundle\Entity\Block;
use DatingLibre\AppBundle\Entity\BlockReason;
use DatingLibre\AppBundle\Repository\BlockReasonRepository;
use DatingLibre\AppBundle\Repository\BlockRepository;
use DatingLibre\AppBundle\Service\UserService;
use DatingLibre\AppBundle\Behat\Page\BlockPage;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

class BlockContext implements Context
{
    private BlockRepository $blockRepository;
    private BlockReasonRepository $blockReasonRepository;
    private UserService $userService;
    private BlockPage $blockPage;

    public function __construct(
        UserService $userService,
        BlockRepository $blockRepository,
        BlockReasonRepository $blockReasonRepository,
        BlockPage $blockPage
    ) {
        $this->userService = $userService;
        $this->blockRepository = $blockRepository;
        $this->blockReasonRepository = $blockReasonRepository;
        $this->blockPage = $blockPage;
    }

    /**
     * @Given the following blocks exist
     */
    public function theFollowingBlocksExist(TableNode $table)
    {
        foreach ($table as $row) {
            $user = $this->userService->findByEmail($row['email']);
            $blockedUser = $this->userService->findByEmail($row['block']);
            Assert::notNull($blockedUser);
            $block = new Block();
            $block->setUser($user);
            $block->setBlockedUser($blockedUser);
            $blockReason = $this->blockReasonRepository->findOneBy([BlockReason::NAME => 'no_reason']);
            $block->setReason($blockReason);

            $this->blockRepository->save($block);
        }
    }

    /**
     * @Then I should see the anonymous profile image
     */
    public function iShouldSeeTheAnonymousProfileImage()
    {
        $this->blockPage->assertContains('profile.jpg');
    }
}
