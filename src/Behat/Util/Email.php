<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat\Util;

class Email
{
    private string $subject;
    private string $body;

    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
