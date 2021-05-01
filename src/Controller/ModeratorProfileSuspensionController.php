<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\CloseSuspensionForm;
use DatingLibre\AppBundle\Form\CloseSuspensionFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ModeratorProfileSuspensionController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private SuspensionService $suspensionService;

    public function __construct(ProfileRepository $profileRepository, SuspensionService $suspensionService)
    {
        $this->profileRepository = $profileRepository;
        $this->suspensionService = $suspensionService;
    }

    public function view(Request $request, Uuid $userId, Uuid $suspensionId)
    {
        $profile = $this->profileRepository->findProjection($userId);
        $suspension = $this->suspensionService->findById($suspensionId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        $closeSuspensionForm = new CloseSuspensionForm();
        $closeSuspensionForm->setSuspension($suspension);

        $closeSuspensionFormType = $this->createForm(CloseSuspensionFormType::class, $closeSuspensionForm);
        $closeSuspensionFormType->handleRequest($request);

        if ($closeSuspensionFormType->isSubmitted() && $closeSuspensionFormType->isValid()) {
            $this->suspensionService->close($this->getUser()->getId(), $closeSuspensionForm->getSuspension()->getId());
            $this->addFlash('success', 'suspension.closed_confirmation');
            return $this->redirectToRoute('moderator_profile_suspensions', ['userId' => $userId]);
        }

        return $this->render('@DatingLibreApp/moderator/profile/suspension.html.twig', [
            'closeSuspensionForm' => $closeSuspensionFormType->createView(),
            'profile' => $profile,
        ]);
    }
}