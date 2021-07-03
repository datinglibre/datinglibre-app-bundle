<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Command;

use DatingLibre\AppBundle\Service\UserArchiveService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeUserArchivesCommand extends Command
{
    protected static $defaultName = 'app:user_archives:purge';
    private UserArchiveService $userArchiveService;

    public function __construct(UserArchiveService $userArchiveService)
    {
        parent::__construct();
        $this->userArchiveService = $userArchiveService;
    }

    protected function configure()
    {
        $this ->setDescription('Deletes user archives periodically');

        $this->addArgument(
            'days',
            InputArgument::REQUIRED,
            'Age of the user archive in days'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userArchiveService->deleteOlderThanDays((int) $input->getArgument('days'));

        return 0;
    }
}
