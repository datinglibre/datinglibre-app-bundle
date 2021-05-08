<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\ConfirmPermanentSuspensionFormType;
use DatingLibre\AppBundle\Form\PermanentSuspensionForm;
use DatingLibre\AppBundle\Form\PermanentSuspensionFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Service\SuspensionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class AdminProfilePermanentlySuspendController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private SuspensionService $suspensionService;

    public function __construct(ProfileRepository $profileRepository, SuspensionService $suspensionService)
    {
        $this->profileRepository = $profileRepository;
        $this->suspensionService = $suspensionService;
    }

    public function view(Request $request, Uuid $userId)
    {
        $profile = $this->profileRepository->findProjection($userId);

        if ($profile === null) {
            throw $this->createNotFoundException();
        }

        $existingPermanentSuspension = $this->suspensionService->findOpenPermanentSuspension($userId);
        $permanentSuspensionForm = new PermanentSuspensionForm();

        if ($existingPermanentSuspension !== null) {
            $permanentSuspensionForm->setReasons($existingPermanentSuspension->getReasons());
        }

        $confirmForm = $this->createForm(PermanentSuspensionFormType::class, $permanentSuspensionForm);
        $confirmForm->handleRequest($request);

        if ($confirmForm->isSubmitted() && $confirmForm->isValid()) {
            $this->suspensionService->permanentlySuspend(
                $this->getUser()->getId(),
                $userId,
                $permanentSuspensionForm->getReasons()
            );

            $this->addFlash('success', 'suspension.permanently_suspended');
            return $this->redirectToRoute('moderator_profile_view', ['userId' => $userId]);
        }

        return $this->render('@DatingLibreApp/admin/profile/permanent_suspension.html.twig', [
            'profile' => $profile,
            'confirmPermanentSuspensionForm' => $confirmForm->createView()
        ]);
    }
}
