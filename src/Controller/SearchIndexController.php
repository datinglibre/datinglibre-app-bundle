<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\Filter;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Form\FilterFormType;
use DatingLibre\AppBundle\Form\RequirementsForm;
use DatingLibre\AppBundle\Form\RequirementsFormType;
use DatingLibre\AppBundle\Repository\FilterRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use DatingLibre\AppBundle\Service\ProfileService;
use DatingLibre\AppBundle\Service\RequirementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class SearchIndexController extends AbstractController
{
    private ProfileService $profileService;
    private UserRepository $userRepository;
    private FilterRepository $filterRepository;
    private RequirementService $requirementService;
    protected const PREVIOUS = 'previous';
    protected const NEXT = 'next';
    protected const LIMIT = 10;

    public function __construct(
        ProfileService $profileService,
        UserRepository $userRepository,
        FilterRepository $filterRepository,
        RequirementService $requirementService
    ) {
        $this->profileService = $profileService;
        $this->userRepository = $userRepository;
        $this->filterRepository = $filterRepository;
        $this->requirementService = $requirementService;
    }

    public function index(UserInterface $user, Request $request)
    {
        if (null === $this->profileService->find($user->getId())) {
            $this->addFlash('warning', 'profile.incomplete');
            return new RedirectResponse($this->generateUrl('profile_edit'));
        }

        $user = $this->userRepository->find($this->getUser()->getId());
        $profile = $this->profileService->find($user->getId());
        $filter = $this->filterRepository->find($user->getId()) ?? $this->createDefaultFilter($user);

        $filterForm = $this->createForm(
            FilterFormType::class,
            $filter,
            ['regions' => $profile->getCity()->getRegion()->getCountry()->getRegions()]
        );


        $requirements = new RequirementsForm();
        $requirements->setColors($this->requirementService->getMultipleByUserAndCategory($user->getId(), 'color'));
        $requirements->setShapes($this->requirementService->getMultipleByUserAndCategory($user->getId(), 'shape'));
        $requirementsForm = $this->createForm(RequirementsFormType::class, $requirements);

        $filterForm->handleRequest($request);
        $requirementsForm->handleRequest($request);

        if ($requirementsForm->isSubmitted() && $requirementsForm->isValid()) {
            $this->requirementService->createRequirementsInCategory(
                $user,
                'color',
                $requirementsForm->getData()->getColors()
            );

            $this->requirementService->createRequirementsInCategory(
                $user,
                'shape',
                $requirementsForm->getData()->getShapes()
            );
        }

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $this->filterRepository->save($filter);
            return new RedirectResponse($this->generateUrl('search_index'));
        }

        $previous = (int) $request->query->get(self::PREVIOUS, 0);
        $next = (int) $request->query->get(self::NEXT, 0);

        $profiles = $this->profileService->findByLocation(
            $user->getId(),
            $filter->getDistance(),
            empty($filter->getRegion()) ? null : $filter->getRegion()->getId(),
            $filter->getMinAge(),
            $filter->getMaxAge(),
            $previous,
            $next,
            self::LIMIT
        );

        return $this->render('@DatingLibreApp/search/index.html.twig', [
            'next' => $this->getNext($profiles, $previous),
            'previous' => $this->getPrevious($profiles, $next),
            'page' => 'search_index',
            'profiles' => $profiles,
            'filterForm' => $filterForm->createView(),
            'requirementsForm' => $requirementsForm->createView()
        ]);
    }

    public function createDefaultFilter(User $user): Filter
    {
        $filter = new Filter();
        $filter->setUser($user);
        return $this->filterRepository->save($filter);
    }

    private function getPrevious(array &$profiles, ?int $next): array
    {
        if ($next !== 0) {
            return [self::PREVIOUS => $next - 1];
        }

        if (count($profiles) === self::LIMIT + 1) {
            $previous = array_shift($profiles);
            return [self::PREVIOUS => $previous->getSortId()];
        }

        return [];
    }

    private function getNext(array &$profiles, ?int $previous): array
    {
        if ($previous !== 0) {
            return [self::NEXT => $previous + 1];
        }

        if (count($profiles) === self::LIMIT + 1) {
            $next = array_pop($profiles);
            return [self::NEXT => $next->getSortId()];
        }

        return [];
    }
}
