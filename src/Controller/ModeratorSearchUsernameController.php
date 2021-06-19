<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Form\EmailSearchForm;
use DatingLibre\AppBundle\Form\EmailSearchFormType;
use DatingLibre\AppBundle\Form\UsernameSearchForm;
use DatingLibre\AppBundle\Form\UsernameSearchFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModeratorSearchUsernameController extends AbstractController
{
    private ProfileRepository $profileRepository;

    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function index(Request $request): Response
    {
        $usernameSearchForm = new UsernameSearchForm();
        $usernameSearchFormType = $this->createForm(UsernameSearchFormType::class, $usernameSearchForm);
        $usernameSearchFormType->handleRequest($request);
        $profile = null;

        if ($usernameSearchFormType->isSubmitted() && $usernameSearchFormType->isValid()) {
            $profile = $this->profileRepository->findProjectionByUsername($usernameSearchForm->getUsername());
        }

        return $this->render('@DatingLibreApp/moderator/search/username/index.html.twig', [
            'usernameSearchForm' => $usernameSearchFormType->createView(),
            'profile' => $profile
        ]);
    }
}
