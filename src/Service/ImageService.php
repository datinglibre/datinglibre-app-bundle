<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Image;
use DatingLibre\AppBundle\Entity\ImageProjection;
use DatingLibre\AppBundle\Repository\ImageRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Aws\S3\S3Client;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Uid\Uuid;

class ImageService
{
    private ImageRepository $imageRepository;
    private UserRepository $userRepository;
    private S3Client $s3Client;
    private string $imagesBucket;
    // pre-signed URLs max expiry is 7 days, make it a bit earlier so enough time to refresh them
    protected const EXPIRY_INTERVAL = 'P6D';

    public function __construct(
        string $imagesBucket,
        S3Service $s3Service,
        ImageRepository $imageRepository,
        UserRepository $userRepository
    ) {
        $this->imageRepository = $imageRepository;
        $this->userRepository = $userRepository;
        $this->s3Client = $s3Service->getClient();
        $this->imagesBucket = $imagesBucket;
    }

    public function save(Uuid $userId, $payload, string $type, bool $isProfile): Image
    {
        $user = $this->userRepository->find($userId);

        // only support one image at the moment
        $this->deleteByUserId($user->getId());

        $image = new Image();
        $image->setUser($user);
        $image->setType($type);
        $image->setIsProfile($isProfile);
        $image = $this->imageRepository->save($image);
        $this->sendToS3($image, $payload);
        $this->setPresignedUrl($image);

        return $this->imageRepository->save($image);
    }

    public function findUnmoderated(): ?ImageProjection
    {
        return $this->imageRepository->findUnmoderated();
    }

    public function accept(string $id)
    {
        $this->saveState($id, Image::ACCEPTED);
    }

    public function reject(string $id)
    {
        $this->saveState($id, Image::REJECTED);
    }

    public function saveState(string $id, string $state): void
    {
        $image = $this->imageRepository->find($id);

        if ($image === null) {
            return;
        }

        $image->setState($state);
        $this->imageRepository->save($image);
    }

    public function delete(string $bucket, Image $image): void
    {
        $this->s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $image->getFilename()
            ]);

        $this->imageRepository->delete($image);
    }

    public function deleteByUserId(?Uuid $userId): void
    {
        $image = $this->imageRepository->findOneBy(['user' => $userId]);
        if ($image !== null) {
            $this->delete($this->imagesBucket, $image);
        }
    }

    private function sendToS3(Image $image, $payload): void
    {
        $this->s3Client->putObject([
            'Bucket' => $this->imagesBucket,
            'Key' => $image->getFilename(),
            'Body' => $payload
        ]);
    }

    public function findProfileImageProjection(Uuid $userId): ?ImageProjection
    {
        return $this->imageRepository->findProjection($userId, true);
    }

    public function refreshSecureUrls(): void
    {
        /** @var Image $expired */
        $expiredImages = $this->imageRepository->findByExpiredSecureUrl();

        foreach ($expiredImages as $expiredImage) {
            $this->setPresignedUrl($expiredImage);
            $this->imageRepository->save($expiredImage);
        }
    }

    private function setPresignedUrl(Image $image): void
    {
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->imagesBucket,
            'Key' => $image->getFilename()
        ]);

        $expiry = $this->getExpiryDateTime();
        $secureUrl = $this->s3Client->createPresignedRequest($command, $expiry);
        $image->setSecureUrl((string) $secureUrl->getUri());
        $image->setSecureUrlExpiry($expiry);
    }

    private function getExpiryDateTime(): DateTimeInterface
    {
        return (new DateTime())->add(new DateInterval(self::EXPIRY_INTERVAL));
    }
}
