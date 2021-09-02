<?php

namespace whatwedo\SwissZip\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\SwissZip\Manager\SwissZipManager;

class SwissZipStripDataCommand extends Command
{

    protected static $defaultName = 'whatwedo:swisszip:strip-data';
    private SwissZipManager $swissZipManager;


    public function __construct(string $name = null, ContainerInterface $container, SwissZipManager $swissZipManager)
    {
        parent::__construct($name);
        $this->swissZipManager = $swissZipManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataLocation = $this->swissZipManager->getDataLocation(false);

        $file_get_contents = file_get_contents($dataLocation);
        $data = json_decode($file_get_contents);


        foreach ($data->records as $item) {
            if (isset($item->geometry)) {
                unset($item->geometry);
            }
            if (isset($item->fields->geo_point_2d)) {
                unset($item->fields->geo_point_2d);
            }
            if (isset($item->fields->geo_shape)) {
                unset($item->fields->geo_shape);
            }
        }

        file_put_contents($dataLocation,  json_encode($data));

        return Command::SUCCESS;
    }


}
