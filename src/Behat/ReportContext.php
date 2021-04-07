<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat;

use Behat\Behat\Context\Context;
use DatingLibre\AppBundle\Entity\Report;
use DatingLibre\AppBundle\Repository\ReportRepository;
use DatingLibre\AppBundle\Service\UserService;
use Webmozart\Assert\Assert;

class ReportContext implements Context
{
    private UserService $userService;
    private ReportRepository $reportRepository;
    public function __construct(
        UserService $userService,
        ReportRepository $reportRepository
    ) {
        $this->userService = $userService;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @Then a report should exist for :reportedUserEmail from :reporterEmail
     */
    public function aReportShouldExist(string $reportedEmail, string $reporterEmail)
    {
        $reportedUser = $this->userService->findByEmail($reportedEmail);
        Assert::notNull($reportedUser);

        $reporterUser = $this->userService->findByEmail($reporterEmail);
        Assert::notNull($reporterUser);
    }

    /**
     * @Given the user :reporterEmail has reported :reportedEmail
     */
    public function createReport(string $reporterEmail, string $reportedEmail)
    {
        $reporter = $this->userService->findByEmail($reporterEmail);
        Assert::notNull($reporter);
        $reported = $this->userService->findByEmail($reportedEmail);
        Assert::notNull($reported);
        $report = new Report();
        $report->setUser($reporter);
        $report->setReportedUser($reported);
        $report->setReasons(['spam']);
        $this->reportRepository->save($report);
    }
}
