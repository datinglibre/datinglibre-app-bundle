<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\ModerateForm;
use DatingLibre\AppBundle\Form\ModerateFormType;
use DatingLibre\AppBundle\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ModeratorImageController extends AbstractController
{
    protected const ACCEPT = 'accept';
    protected const REJECT = 'reject';
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $moderateFormData = new ModerateForm();
        $profileImageProjection = $this->imageService->findUnmoderated();

        $moderateForm = $this->createForm(ModerateFormType::class, $moderateFormData);

        $moderateForm->handleRequest($request);
        if ($moderateForm->isSubmitted() && $moderateForm->isValid()) {
            if ($moderateForm->get(self::ACCEPT)->isClicked()) {
                $this->imageService->accept($profileImageProjection->getId());
                $this->addFlash('success', 'moderate.accepted');
            } else {
                $this->addFlash('warning', 'moderate.rejected');
                $this->imageService->reject($profileImageProjection->getId());
            }

            return new RedirectResponse($this->generateUrl('moderator_profile_images'));
        }

        return $this->render('@DatingLibreApp/moderator/images.html.twig', [
            'profileImage' => $profileImageProjection,
            'moderateForm' => $moderateForm->createView()
        ]);
    }
}
