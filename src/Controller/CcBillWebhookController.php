<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\CcBillService;
use DatingLibre\CcBill\Mapper\CcBillEventMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CcBillWebhookController extends AbstractController
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

        $this->ccBillService->processEvent(CcBillEventMapper::mapEvent(
            $request->query->get(self::EVENT_TYPE),
            $event
        ));

        return new Response();
    }
}
