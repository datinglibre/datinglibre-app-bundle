<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\BlockFormType;
use DatingLibre\AppBundle\Service\BlockService;
use DatingLibre\AppBundle\Service\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class UserBlockDeleteController extends AbstractController
{
    private ProfileService $profileService;
    private BlockService $blockService;

    public function __construct(ProfileService $profileService, BlockService $blockService)
    {
        $this->profileService = $profileService;
        $this->blockService = $blockService;
    }

    public function delete(Request $request, Uuid $userId)
    {
        $profile = $this->profileService->findProjectionByCurrentUser($this->getUser()->getId(), $userId);

        if (null === $profile) {
            throw $this->createNotFoundException();
        }

        $blockFormType = $this->createForm(BlockFormType::class);
        $blockFormType->handleRequest($request);

        if ($blockFormType->isSubmitted() && $blockFormType->isValid()) {
            $this->blockService->unblock($this->getUser()->getId(), $userId);

            $this->addFlash('success', 'unblock.success');
            return $this->redirectToRoute('user_profile_view', ['userId' => $profile->getId()]);
        }

        return $this->render(
            '@DatingLibreApp/user/block/delete.html.twig',
            [
                'blockForm' => $blockFormType->createView(),
                'profile' => $profile
            ]
        );
    }
}
