<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\BlockFormType;
use DatingLibre\AppBundle\Service\BlockService;
use DatingLibre\AppBundle\Service\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class BlockController extends AbstractController
{
    public function block(Uuid $userId, Request $request, ProfileService $profileService, BlockService $blockService)
    {
        $profile = $profileService->findProjectionByCurrentUser($this->getUser()->getId(), $userId);

        if (null === $profile) {
            throw $this->createNotFoundException();
        }

        $blockFormType = $this->createForm(BlockFormType::class);
        $blockFormType->handleRequest($request);

        if ($blockFormType->isSubmitted() && $blockFormType->isValid()) {
            $blockService->block($this->getUser()->getId(), $userId);

            $this->addFlash('success', 'block.success');
            return $this->redirectToRoute('search_index');
        }

        return $this->render(
            '@DatingLibreApp/user/block/create.html.twig',
            [
                'controller_name' => 'BlockController',
                'blockForm' => $blockFormType->createView(),
                'profile' => $profile
            ]
        );
    }
}
