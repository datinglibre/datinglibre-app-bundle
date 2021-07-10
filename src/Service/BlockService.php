<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Block;
use DatingLibre\AppBundle\Repository\BlockRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlockService
{
    private BlockRepository $blockRepository;
    private UserRepository $userRepository;

    public function __construct(BlockRepository $blockRepository, UserRepository $userRepository)
    {
        $this->blockRepository = $blockRepository;
        $this->userRepository = $userRepository;
    }

    public function block(
        Uuid $currentUserId,
        Uuid $userToBlockId
    ) {
        $currentUser = $this->userRepository->find($currentUserId);
        $userToBlock = $this->userRepository->find($userToBlockId);

        if (null === $currentUser || null === $userToBlock) {
            throw new NotFoundHttpException('Could not find user to block');
        }

        $block = new Block();
        $block->setUser($currentUser);
        $block->setBlockedUser($userToBlock);

        $this->blockRepository->save($block);
    }

    public function findByUserId(Uuid $userId): array
    {
        return $this->blockRepository->findProfileProjectionsByUserId($userId);
    }

    public function unblock(Uuid $userId, Uuid $blockedUserId): void
    {
        $this->blockRepository->deleteByUserIdAndBlockedUserId($userId, $blockedUserId);
    }
}
