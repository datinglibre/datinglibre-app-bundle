<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Tests\Service;

use DatingLibre\AppBundle\Entity\Token;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Repository\TokenRepository;
use DatingLibre\AppBundle\Service\TokenService;
use DateInterval;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class TokenServiceTest extends TestCase
{
    private TokenService $tokenService;
    private TokenGeneratorInterface $tokenGeneratorInterfaceMock;
    private TokenRepository $tokenRepositoryMock;

    public function setUp(): void
    {
        /** @var TokenRepository tokenRepository */
        $this->tokenRepositoryMock = $this->getMockBuilder(TokenRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TokenGeneratorInterface tokenGeneratorInterface */
        $this->tokenGeneratorInterfaceMock = $this->getMockBuilder(TokenGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenService = new TokenService($this->tokenRepositoryMock, $this->tokenGeneratorInterfaceMock);
    }

    public function provider()
    {
        return [[new DateTime(), true],
            [(new DateTime())->sub(new DateInterval('P1D')), false],
            [(new DateTime())->sub(new DateInterval('PT1H')), true]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testTokenPassesVerificationIfNotOlderThanADay(DateTimeInterface $dateTime, bool $result): void
    {
        $user = $this->getMockBuilder(User::class)
            ->getMock();

        $testSecret = 'abc';

        $user->method('getId')
            ->willReturn(Uuid::v4());

        $token = $this->getMockBuilder(Token::class)
            ->getMock();

        $token->method('getCreatedAt')
            ->willReturn($dateTime);

        $this->tokenRepositoryMock->method('findOneBy')
            ->willReturn($token);

        $this->assertEquals($this->tokenService->verify($user, $testSecret, Token::SIGNUP), $result);
    }
}
