<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\CcBillService;
use DatingLibre\CcBillEventParser\Parser\CcBillEventParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CcBillWebhookController
{
    private const UTF_8 = 'utf-8';
    private const EVENT_TYPE = 'eventType';
    private CcBillService $ccBillService;

    public function __construct(CcBillService $ccBillService)
    {
        $this->ccBillService = $ccBillService;
    }

    public function webhook(Request $request)
    {
        $event = json_decode(mb_convert_encoding($request->getContent(), self::UTF_8, self::UTF_8), true);

        $this->ccBillService->processEvent(CcBillEventParser::parseEvent(
            $request->query->get(self::EVENT_TYPE),
            $event
        ));

        return new Response();
    }
}
