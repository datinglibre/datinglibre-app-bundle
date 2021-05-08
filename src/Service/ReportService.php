<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Service;

use DatingLibre\AppBundle\Entity\Report;
use DatingLibre\AppBundle\Entity\ReportProjection;
use DatingLibre\AppBundle\Repository\ReportRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class ReportService
{
    private ReportRepository $reportRepository;
    private UserRepository $userRepository;

    public function __construct(ReportRepository $reportRepository, UserRepository $userRepository)
    {
        $this->reportRepository = $reportRepository;
        $this->userRepository = $userRepository;
    }

    public function report(Uuid $reporterId, Uuid $reportedId, string $message, array $reportReasons): void
    {
        $user = $this->userRepository->findOneBy(['id' => $reporterId]);
        $reportedUser = $this->userRepository->findOneBy(['id' => $reportedId]);

        if ($reportedUser === null) {
            throw new NotFoundHttpException('Could not find user to report');
        }

        $report = new Report();
        $report->setUser($user);
        $report->setReportedUser($reportedUser);
        $report->setMessage($message);
        $report->setReasons($reportReasons);

        $this->reportRepository->save($report);
    }

    public function find($reporterId, Uuid $reportedId): ?Report
    {
        return $this->reportRepository->findOneBy(
            [
                'user' => $reporterId,
                'reportedUser' => $reportedId
            ]
        );
    }

    public function findById(Uuid $reportId): Report
    {
        return $this->reportRepository->find($reportId);
    }

    public function findCreated(): array
    {
        return $this->reportRepository->findByStatus(Report::OPEN);
    }

    public function findProjectionById(Uuid $reportId): ReportProjection
    {
        $report = $this->reportRepository->findById($reportId);

        if ($report === null) {
            throw new NotFoundHttpException();
        }

        return $report;
    }

    public function findByUserId(Uuid $userId): array
    {
        return $this->reportRepository->findByUserId($userId);
    }

    public function close(Uuid $moderatorId, Uuid $reportId): Report
    {
        $report = $this->reportRepository->find($reportId);
        $moderator = $this->userRepository->find($moderatorId);
        $report->setStatus(Report::CLOSED);
        $report->setUserClosed($moderator);
        return $this->reportRepository->save($report);
    }
}
