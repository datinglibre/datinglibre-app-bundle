<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat\Util;

use Symfony\Component\HttpClient\HttpClient;
use Webmozart\Assert\Assert;

class EmailUtil
{
    private const EMAIL_REST_URL = "http://%s:8025/api/v2/search?kind=to&query=%s&limit=1";
    private const DELETE_EMAILS_URL = "http://%s:8025/api/v1/messages";

    public static function getEmail(string $email): Email
    {
        $emailResponse = HttpClient::create()
            ->request('GET', sprintf(self::EMAIL_REST_URL, 'localhost', $email));

        Assert::eq($emailResponse->getStatusCode(), 200);
        $emails = json_decode($emailResponse->getContent(), true);

        return new Email(
            $emails['items'][0]['Content']['Headers']['Subject'][0],
            quoted_printable_decode($emails['items'][0]['Content']['Body'])
        );
    }

    public static function deleteAll()
    {
        $response = HttpClient::create()
            ->request('DELETE', sprintf(self::DELETE_EMAILS_URL, 'localhost'));

        Assert::eq($response->getStatusCode(), 200);
    }
}
