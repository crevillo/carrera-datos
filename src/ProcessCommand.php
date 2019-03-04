<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('data-race');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();

        $filesPath = getenv("HOME") . "/data-race/";

        $downloader = new Downloader(
            new \Google\Cloud\Storage\StorageClient([
                'keyFilePath' => './gcloud-account.json',
                'projectId' => 'tce-sistemas-14702046268333'
            ]),
            $fileSystem,
            $filesPath
        );
        $start = microtime(true);
        $downloader->downloadSourceFiles(false);
        $downloadDataTime = microtime(true) - $start;

        $reader = new Reader(
            $filesPath,
            $fileSystem
        );

        $start = microtime(true);
        $data = $reader->getData();
        $readDataTime = microtime(true) - $start;

        $writer = new Writer(
            $filesPath,
            $fileSystem
        );

        $start = microtime(true);
        $writer->writeResults($data);
        $writeDataTime = microtime(true) - $start;

        $table = new Table($output);
        $table
            ->setHeaders(['Parte', 'Tiempo en segundos'])
            ->setRows([
                ['Descarga', $downloadDataTime],
                ['Lectura y proceso de archivos', $readDataTime],
                ['Escritura del fichero', $writeDataTime]
            ])
        ;
        $table->render();
    }
}
