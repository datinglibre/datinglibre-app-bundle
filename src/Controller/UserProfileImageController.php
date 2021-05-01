<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\ImageService;
use DatingLibre\AppBundle\Service\ProfileService;
use Gumlet\ImageResize;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserProfileImageController extends AbstractController
{
    private ImageService $imageService;
    private ProfileService $profileService;
    private bool $imageUpload;
    private const HEIGHT = 255;
    private const WIDTH = 255;

    public function __construct(
        ImageService $imageService,
        ProfileService $profileService,
        bool $imageUpload
    ) {
        $this->imageService = $imageService;
        $this->profileService = $profileService;
        $this->imageUpload = $imageUpload;
    }

    public function index(Request $request)
    {
        $userId = $this->getUser()->getId();

        if ($request->isMethod('POST')) {
            if (!$this->imageUpload) {
                throw $this->createAccessDeniedException();
            }

            $image = $request->files->get('image', null);

            if ($image != null) {
                $image = new ImageResize($image->getRealPath());
                $image->resize(self::HEIGHT, self::WIDTH);
                $this->imageService->save($userId, $image->getImageAsString(), 'jpg', true);
            }
        }

        return $this->render('@DatingLibreApp/user/profile/image.html.twig', [
            'profile' => $this->profileService->findProjection($userId),
            'imageUpload' => $this->imageUpload
        ]);
    }
}
