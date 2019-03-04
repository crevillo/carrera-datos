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
        $executionStart = microtime(true);

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
            $fileSystem,
            $output
        );

        $start = microtime(true);
        $data = $reader->getData();
        $readDataTime = microtime(true) - $start;

        $writer = new Writer(
            $filesPath,
            $fileSystem
        );

        $start = microtime(true);
        $writer->writeResults($data['data']);
        $writeDataTime = microtime(true) - $start;

        $table = new Table($output);
        $table
            ->setHeaders(['Parte', 'Tiempo en segundos']);

        if ($downloadDataTime > 0) {
            $table->addRow(['Descarga', $downloadDataTime]);
        }

        foreach ($data['timers'] as $file => $time) {
            $table->addRow(["Lectura y procesado de " . $file, $time]);
        }

        $table->addRows([
            ['Lectura y proceso de archivos en total', $readDataTime],
            ['Escritura del fichero', $writeDataTime],
            ['Tiempo total', microtime(true) - $executionStart]
        ]);

        $table->render();
    }
}
