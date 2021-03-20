<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Form;

use DateTimeImmutable;

class EventFilterForm
{
    private int $year;
    private int $month;
    private ?int $day;

    public function __construct()
    {
        $now = new DateTimeImmutable('now');
        $this->year = (int) $now->format('Y');
        $this->month = (int) $now->format('n');
        $this->day = (int) $now->format('j');
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): void
    {
        $this->month = $month;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(?int $day): void
    {
        $this->day = $day;
    }
}
