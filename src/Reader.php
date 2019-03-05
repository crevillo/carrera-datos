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

    private $races = [];

    public function __construct(string $filesDir, Filesystem $fileSystem, OutputInterface $output)
    {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->output = $output;
    }

    public function getData()
    {
        $this->readRaces();

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

    private function readRaces()
    {
        $csv = fopen($this->filesDir . 'race.csv', 'r');

        $i = 0;
        while (($currRow = fgetcsv($csv, 256)) !== FALSE) {
            if ($i > 0) {
                $this->races[(string)$currRow[0]] = $currRow[1];
            }

            $i++;
        }
    }

    private function readNatalityFile($file)
    {
        $csv = fopen($this->filesDir . $file, 'r');

        $i = 0;
        while (($currRow = fgetcsv($csv, 256)) !== FALSE) {
            if ($i > 0) {
                $this->processLine($currRow);
            }
            $i++;
        }
    }

    private function processLine($line)
    {
        $state = $line[5];

        $bornDecade = substr($line[1], 0, -1) . '0';
        $childRace = $line[7];
        $isMale = $line[6];
        $weightPounds = (float)$line[8];

        if (!isset($this->results[$state])) {
            $this->results[$state] = [
                'births' => [],
                'male' => 0,
                'female' => 0,
                'weight' => [
                    'total' => 0,
                    'numberOfBorns' => 0,
                ]
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
