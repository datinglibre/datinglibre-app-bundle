<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\Profile;
use DatingLibre\AppBundle\Form\ProfileForm;
use DatingLibre\AppBundle\Form\ProfileFormType;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use DatingLibre\AppBundle\Service\ProfileService;
use DatingLibre\AppBundle\Service\UserAttributeService;
use DatingLibre\AppBundle\Repository\CountryRepository;
use DatingLibre\AppBundle\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ProfileEditController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private UserRepository $userRepository;
    private RegionRepository $regionRepository;
    private CountryRepository $countryRepository;
    private UserAttributeService $userAttributeService;
    private ProfileService $profileService;

    public function __construct(
        ProfileRepository $profileRepository,
        ProfileService $profileService,
        UserRepository $userRepository,
        RegionRepository $regionRepository,
        CountryRepository $countryRepository,
        UserAttributeService $userAttributeService
    ) {
        $this->profileRepository = $profileRepository;
        $this->userRepository = $userRepository;
        $this->regionRepository = $regionRepository;
        $this->countryRepository = $countryRepository;
        $this->userAttributeService = $userAttributeService;
        $this->profileService = $profileService;
    }

    public function edit(Request $request)
    {
        $userId = $this->getUser();
        $user = $this->userRepository->find($userId);
        $profile = $this->profileRepository->find($userId) ?? (new Profile())->setUser($user);
        $profileProjection = $this->profileService->findProjection($user->getId());

        $profileForm = new ProfileForm();
        $city = $profile->getCity();

        if ($city != null) {
            $profileForm->setCountry($city->getCountry());
            $profileForm->setRegion($city->getRegion());
            $profileForm->setCity($city);
        }

        $profileForm->setAbout($profile->getAbout());
        $profileForm->setUsername($profile->getUsername());
        $profileForm->setDob($profile->getDob());
        $profileForm->setColor($this->userAttributeService->getOneByCategoryName($user, 'color'));
        $profileForm->setShape($this->userAttributeService->getOneByCategoryName($user, 'shape'));

        $profileFormType = $this->createForm(ProfileFormType::class, $profileForm);
        $profileFormType->handleRequest($request);

        if ($profileFormType->isSubmitted() && $profileFormType->isValid()) {
            $this->userAttributeService->createUserAttributes(
                $user,
                [$profileFormType->getData()->getColor(), $profileFormType->getData()->getShape()]
            );

            $profile->setCity($profileFormType->getData()->getCity());
            $profile->setUsername($profileFormType->getData()->getUsername());
            $profile->setAbout($profileFormType->getData()->getAbout());
            $profile->setDob($profileFormType->getData()->getDob());
            $this->profileRepository->save($profile);
            return new RedirectResponse($this->generateUrl('profile_index'));
        }

        return $this->render('@DatingLibreApp/profile/edit.html.twig', [
            'controller_name' => 'ProfileEditController',
            'profileForm' => $profileFormType->createView(),
            'profile' => $profileProjection,
        ]);
    }
}
