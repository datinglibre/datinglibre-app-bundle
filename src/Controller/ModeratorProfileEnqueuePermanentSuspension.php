<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\PermanentSuspensionForm;
use DatingLibre\AppBundle\Form\PermanentSuspensionFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ModeratorProfileEnqueuePermanentSuspension extends AbstractController
{
    private ProfileRepository $profileRepository;
    private SuspensionService $suspensionService;

    public function __construct(ProfileRepository $profileRepository, SuspensionService $suspensionService)
    {
        $this->profileRepository = $profileRepository;
        $this->suspensionService = $suspensionService;
    }

    public function enqueue(Request $request, Uuid $userId)
    {
        $profile = $this->profileRepository->findProjection($userId);
        $permanentSuspension = $this->suspensionService->findOpenPermanentSuspension($userId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        $permanentSuspensionForm = new PermanentSuspensionForm();
        $enqueuePermanentSuspensionForm = $this->createForm(PermanentSuspensionFormType::class, $permanentSuspensionForm);
        $enqueuePermanentSuspensionForm->handleRequest($request);

        if ($enqueuePermanentSuspensionForm->isSubmitted() && $enqueuePermanentSuspensionForm->isValid()) {
            $this->suspensionService->enqueuePermanentSuspension(
                $this->getUser()->getId(),
                $userId,
                $permanentSuspensionForm->getReasons()
            );
            return $this->redirectToRoute('moderator_profile_enqueue_permanent_suspension', ['userId' => $userId]);
        }

        return $this->render('@DatingLibreApp/moderator/profile/permanent_suspension.html.twig', [
            'profile' => $profile,
            'permanentSuspension' => $permanentSuspension,
            'enqueuePermanentSuspensionForm' => $enqueuePermanentSuspensionForm->createView()
        ]);
    }
}
