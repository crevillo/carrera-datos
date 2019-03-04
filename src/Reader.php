<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 2019-03-04
 * Time: 06:55
 */

namespace App;

use Symfony\Component\Filesystem\Filesystem;

class Reader
{
    private $filesDir;

    private $fileSystem;

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

    public function __construct(string $filesDir, Filesystem $fileSystem)
    {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
    }

    public function getData()
    {
        $files = scandir($this->filesDir);

        foreach ($files as $file) {
            if (strpos($file, 'natalidad') !== false) {
                $this->readNatalityFile($file);
            }
        }

        ksort($this->results);

        foreach ($this->results as $state => $date) {
            ksort($this->results[$state]);
        }

        return $this->results;
    }

    private function readNatalityFile($file)
    {
        print $file . "\n";
        $csv = new \SplFileObject($this->filesDir . $file);
        $csv->setFlags(\SplFileObject::READ_CSV);

        $start = 1;
        $batch = 200000;
        while (!$csv->eof()) {
            foreach (new \LimitIterator($csv, $start, $batch) as $line) {
                $this->processLine($line);
            }
            $start += $batch;
        }
    }

    private function processLine($line)
    {
        if (!isset($line[5])) {
            return;
        }

        $state = $line[5];
        $bornDecade = round($line[1] / 10) * 10;
        $childRace = $line[7];
        $isMale = (bool)$line[6];

        if (!isset($this->results[$state])) {
            $this->results[$state] = [];
        }

        if (!isset($this->results[$state][$bornDecade])) {
            $this->results[$state][$bornDecade]['borns'] = 0;
            $this->results[$state][$bornDecade]['male'] = 0;
            $this->results[$state][$bornDecade]['female'] = 0;
            $this->results[$state][$bornDecade]['races'] = [];
            foreach ($this->races as $key => $raceName) {
                $this->results[$state][$bornDecade]['races'][$raceName] = 0;
            }
        }

        $this->results[$state][$bornDecade]['borns']++;

        if (isset($this->races[$childRace])) {
            $raceName = $this->races[$childRace];
            $this->results[$state][$bornDecade]['races'][$raceName]++;
        }

        if ($isMale) {
            $this->results[$state][$bornDecade]['male']++;
        } else {
            $this->results[$state][$bornDecade]['female']++;
        }
    }
}
