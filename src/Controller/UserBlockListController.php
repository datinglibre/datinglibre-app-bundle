<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\BlockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserBlockListController extends AbstractController
{
    private BlockService $blockService;

    public function __construct(BlockService $blockService)
    {
        $this->blockService = $blockService;
    }

    public function index()
    {
        return $this->render(
            '@DatingLibreApp/user/block/list.html.twig',
            [
                'profiles' => $this->blockService->findByUserId($this->getUser()->getId())
            ]
        );
    }
}
