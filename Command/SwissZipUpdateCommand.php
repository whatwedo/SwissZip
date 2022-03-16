<?php

namespace whatwedo\SwissZip\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use whatwedo\SwissZip\Manager\SwissZipUpdateManager;

class SwissZipUpdateCommand extends Command
{
    public const DELETE = 'delete';

    public const DRY_RUN = 'dry-run';

    protected static $defaultName = 'whatwedo:swisszip:update';

    private SwissZipUpdateManager $swissZipManager;

    private EntityManagerInterface $entityManager;

    public function __construct(SwissZipUpdateManager $swissZipManager, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->swissZipManager = $swissZipManager;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->addOption(self::DELETE, 'd', InputOption::VALUE_NONE, 'delete all entries first');
        $this->addOption(self::DRY_RUN, null, InputOption::VALUE_NONE, 'do not store things');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->entityManager->beginTransaction();

        $updateReport = $this->swissZipManager->update($input->getOption(self::DELETE));
        if ($input->getOption(self::DRY_RUN)) {
            $this->entityManager->rollback();
        } else {
            $this->entityManager->commit();
        }

        if ($input->getOption(self::DRY_RUN)) {
            $io->caution('DRY-RUN');
        }

        $io->horizontalTable([
            'deleted',
            'inserted',
            'updated',
            'skipped',
        ], [
            [$updateReport->deleted,
                $updateReport->inserted,
                $updateReport->updated,
                $updateReport->skipped, ],
        ]);

        if (count($updateReport->getMessages())) {
            $io->note($updateReport->getMessages());
        }

        return Command::SUCCESS;
    }
}
