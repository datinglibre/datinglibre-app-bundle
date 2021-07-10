<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Command;

use DatingLibre\AppBundle\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeUsersCommand extends Command
{
    protected static $defaultName = 'app:users:purge';
    public const ALL = 'ALL';
    public const NOT_ENABLED = 'NOT_ENABLED';
    private UserService $userService;
    private array $testingUserEmailAddresses;

    public function __construct(UserService $userService, array $testingUserEmailAddresses = [])
    {
        parent::__construct();
        $this->userService = $userService;
        $this->testingUserEmailAddresses = $testingUserEmailAddresses;
    }

    protected function configure()
    {
        $this ->setDescription('Deletes users periodically');

        $this->addArgument(
            'type',
            InputArgument::REQUIRED,
            'Type of user you want to purge (ALL, NOT_ENABLED)'
        );

        $this->addArgument(
            'hours',
            InputArgument::REQUIRED,
            'Age of the user in hours'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userService->purge(
            $this->testingUserEmailAddresses,
            $input->getArgument('type'),
            (int) $input->getArgument('hours')
        );

        return 0;
    }
}
