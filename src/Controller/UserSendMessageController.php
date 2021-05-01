<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Entity\Message;
use DatingLibre\AppBundle\Form\MessageForm;
use DatingLibre\AppBundle\Form\MessageFormType;
use DatingLibre\AppBundle\Repository\MessageRepository;
use DatingLibre\AppBundle\Repository\ProfileRepository;
use DatingLibre\AppBundle\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserSendMessageController extends AbstractController
{
    private ProfileRepository $profileRepository;
    private MessageRepository $messageRepository;
    private UserRepository $userRepository;

    public function __construct(
        ProfileRepository $profileRepository,
        UserRepository $userRepository,
        MessageRepository $messageRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
    }

    public function message(Request $request, Uuid $userId)
    {
        $sender = $this->userRepository->find($this->getUser()->getId());
        $recipient = $this->userRepository->find($userId);

        if ($sender === null || $recipient === null) {
            throw $this->createNotFoundException();
        }

        $recipientProfile = $this->profileRepository->findProjectionByCurrentUser(
            $sender->getId(),
            $userId
        );

        if ($recipientProfile === null) {
            throw $this->createNotFoundException();
        }

        $messageForm = new MessageForm();
        $messageFormType = $this->createForm(MessageFormType::class, $messageForm);
        $messageFormType->handleRequest($request);

        if ($messageFormType->isSubmitted() && $messageFormType->isValid()) {
            $message = new Message();
            $message->setContent($messageFormType->getData()->getContent());
            $message->setSender($sender);
            $message->setUser($recipient);

            $this->messageRepository->save($message);

            $this->addFlash('success', 'message.sent');
            return new RedirectResponse($this->generateUrl(
                'user_send_message',
                ['userId' => $userId]
            ));
        }

        return $this->render('@DatingLibreApp/user/message/send.html.twig', [
            'messages' => $this->messageRepository->findMessagesBetweenUsers(
                $sender->getId(),
                $recipient->getId()
            ),
            'profile' => $recipientProfile,
            'messageForm' => $messageFormType->createView(),
            'controller_name' => 'MessageSendController'
        ]);
    }
}
