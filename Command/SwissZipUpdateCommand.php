<?php

namespace whatwedo\SwissZip\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\SwissZip\Manager\SwissZipManager;

class SwissZipUpdateCommand extends Command
{

    const DELETE = 'delete';
    const ONLINE = 'online';
    protected static $defaultName = 'whatwedo:swisszip:update';
    private SwissZipManager $swissZipManager;


    public function __construct(string $name = null, ContainerInterface $container, SwissZipManager $swissZipManager)
    {
        parent::__construct($name);
        $this->swissZipManager = $swissZipManager;
    }

    protected function configure()
    {
        $this->addOption(self::DELETE, 'd', InputOption::VALUE_NONE, 'delete all entries first');
        $this->addOption(self::ONLINE, 'o', InputOption::VALUE_NONE, 'get data online, stead from local file');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updateReport = $this->swissZipManager->update($input->getOption(self::DELETE), $input->getOption(self::ONLINE));

        $output->writeln('data Location: ' . $updateReport->location);
        $output->writeln('deleted: ' . $updateReport->deleted);
        $output->writeln('inserted: ' . $updateReport->inserted);
        $output->writeln('updated: ' . $updateReport->updated);

        return Command::SUCCESS;
    }


}
