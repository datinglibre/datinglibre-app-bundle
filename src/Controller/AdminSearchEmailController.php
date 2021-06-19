<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\EmailSearchForm;
use DatingLibre\AppBundle\Form\EmailSearchFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSearchEmailController extends AbstractController
{
    private ProfileRepository $profileRepository;

    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function index(Request $request): Response
    {
        $emailSearchForm = new EmailSearchForm();
        $emailSearchFormType = $this->createForm(EmailSearchFormType::class, $emailSearchForm);
        $emailSearchFormType->handleRequest($request);
        $profile = null;

        if ($emailSearchFormType->isSubmitted() && $emailSearchFormType->isValid()) {
            $profile = $this->profileRepository->findProjectionByEmail($emailSearchForm->getEmail());
        }

        return $this->render('@DatingLibreApp/admin/search/email/index.html.twig', [
            'emailSearchForm' => $emailSearchFormType->createView(),
            'profile' => $profile
        ]);
    }
}
