<?php

namespace whatwedo\SwissZip\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwissZipUpdateCommand extends Command
{

    protected static $defaultName = 'swisszip:udpate';




    protected function execute(InputInterface $input, OutputInterface $output)
    {

//        file_put_contents('plz_verzeichnis_v2.json', file_get_contents('https://swisspost.opendatasoft.com/api/records/1.0/search/?dataset=plz_verzeichnis_v2&q=&rows=10000'));

        $dir = $this->container->get('kernel')->locateResource('@AcmeDemoBundle/Resource');
        $json = json_decode(file_get_contents('plz_verzeichnis_v2.json'));




        foreach ($json->records as $dataSet) {
            if (isset($dataSet->fields->plz_coff) && $dataSet->fields->plz_coff = 'J') {
                $o = 0;
            }
        }



    }


}
