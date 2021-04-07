<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use DatingLibre\AppBundle\Entity\Report;

class CloseReportForm
{
    private Report $report;

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): void
    {
        $this->report = $report;
    }
}
