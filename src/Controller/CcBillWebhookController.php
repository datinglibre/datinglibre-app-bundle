<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Service\CcBillEventService;
use DatingLibre\CcBill\Mapper\CcBillEventMapper;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CcBillWebhookController extends AbstractController
{
    private const UTF_8 = 'utf-8';
    private const EVENT_TYPE = 'eventType';
    private CcBillEventService $ccBillEventService;

    public function __construct(CcBillEventService $ccBillEventService)
    {
        $this->ccBillEventService = $ccBillEventService;
    }

    /**
     * @throws Exception
     */
    public function webhook(Request $request)
    {
        $event = json_decode(mb_convert_encoding($request->getContent(), self::UTF_8, self::UTF_8), true);

        $this->ccBillEventService->processEvent(CcBillEventMapper::mapEvent(
            $request->query->get(self::EVENT_TYPE),
            $event
        ));

        return new Response();
    }
}
