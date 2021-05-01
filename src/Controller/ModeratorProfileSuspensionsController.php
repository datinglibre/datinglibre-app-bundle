<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\SuspensionForm;
use DatingLibre\AppBundle\Form\SuspensionFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class ModeratorProfileSuspensionsController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private SuspensionService $suspensionService;

    public function __construct(
        ProfileRepository $profileRepository,
        SuspensionService $suspensionService
    ) {
        $this->profileRepository = $profileRepository;
        $this->suspensionService = $suspensionService;
    }

    public function index(Request $request, Uuid $userId): Response
    {
        $profile = $this->profileRepository->findProjection($userId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        $suspensionForm = new SuspensionForm();
        $suspensionFormType = $this->createForm(SuspensionFormType::class, $suspensionForm);
        $suspensionFormType->handleRequest($request);

        if ($suspensionFormType->isSubmitted() && $suspensionFormType->isValid()) {
            $this->suspensionService->suspend(
                $this->getUser()->getId(),
                $userId,
                $suspensionForm->getReasons(),
                $suspensionForm->getDuration()
            );

            $this->addFlash('success', 'suspension.success');
            return new RedirectResponse($this->generateUrl(
                'moderator_profile_suspensions',
                ['userId' => $userId]
            ));
        }

        return $this->render('@DatingLibreApp/moderator/profile/suspensions.html.twig', [
            'profile' => $profile,
            'suspensionForm' => $suspensionFormType->createView(),
            'openSuspension' => $this->suspensionService->findOpenByUserId($userId),
            'suspensions' => $this->suspensionService->findAllByUserId($userId),
        ]);
    }
}
