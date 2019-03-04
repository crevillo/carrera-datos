<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 2019-03-04
 * Time: 06:55
 */

namespace App;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Reader
{
    private $filesDir;

    private $fileSystem;

    private $output;

    private $results;

    private $races = [
        '1' => 'White',
        '2' => 'Black',
        '3' => 'American Indian',
        '4' => 'Chinese',
        '5' => 'Japanese',
        '6' => 'Hawaiian',
        '7' => 'Filipino',
        '9' => 'Unknown/Other',
        '18' => 'Asian Indian',
        '28' => 'Korean',
        '39' => 'Samoan',
        '48' => 'Vietnamese'
    ];

    public function __construct(string $filesDir, Filesystem $fileSystem, OutputInterface $output)
    {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->output = $output;
    }

    public function getData()
    {
        $files = scandir($this->filesDir);
        $timers = [];

        foreach ($files as $file) {
            if (strpos($file, 'natalidad') !== false) {
                $this->output->writeln("Empieza lectura de $file");
                $start = microtime(true);
                $this->readNatalityFile($file);
                $timers[$file] = microtime(true) - $start;
                $this->output->writeln("Finaliza lectura de $file");
            }
        }

        ksort($this->results);

        foreach ($this->results as $state => $date) {
            ksort($this->results[$state]);
        }

        return ['data' => $this->results, 'timers' => $timers];
    }

    private function readNatalityFile($file)
    {
        $csv = fopen($this->filesDir . $file, 'r');

        $offset = 0;
        $limit = 100000;
        while(!feof($csv))
        {
            //Go to where we were when we ended the last batch
            fseek($csv, $offset);

            $i = 1;
            while (($currRow = fgetcsv($csv)) !== FALSE)
            {
                $i++;

                $this->processLine($currRow);
                //If we hit our limit or are at the end of the file
                if ($i >= $limit)
                {
                    //Update our current position in the file
                    $offset = ftell($csv);

                    //Break out of the row processing loop
                    break;
                }
            }
        }
    }

    private function processLine($line)
    {
        if (empty($line[5]) || !is_numeric($line[1])) {
            return;
        }

        $state = $line[5];

        $bornDecade = round($line[1] / 10) * 10;
        $childRace = $line[7];
        $isMale = $line[6];
        $weightPounds = (float)$line[8];

        if (!isset($this->results[$state])) {
            $this->results[$state] = [];
            $this->results[$state]['births'] = [];
            $this->results[$state]['male'] = 0;
            $this->results[$state]['female'] = 0;
            $this->results[$state]['weight'] = [
                'total' => 0,
                'numberOfBorns' => 0,
            ];
        }

        if (!isset($this->results[$state]['births'][$bornDecade])) {
            $this->results[$state]['births'][$bornDecade]['borns'] = 0;
            $this->results[$state]['births'][$bornDecade]['races'] = [];
            foreach ($this->races as $key => $raceName) {
                $this->results[$state]['births'][$bornDecade]['races'][$raceName] = 0;
            }
        }

        $this->results[$state]['births'][$bornDecade]['borns']++;

        if (isset($this->races[$childRace])) {
            $raceName = $this->races[$childRace];
            $this->results[$state]['births'][$bornDecade]['races'][$raceName]++;
        }

        if ($isMale == 'true') {
            $this->results[$state]['male']++;
        } else {
            $this->results[$state]['female']++;
        }

        $this->results[$state]['weight']['numberOfBorns']++;
        $this->results[$state]['weight']['total'] += $weightPounds;
    }
}
