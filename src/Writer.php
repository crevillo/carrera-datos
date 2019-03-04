<?php

namespace App;

use Symfony\Component\Filesystem\Filesystem;

class Writer
{
    private $filesPath;

    private $fileSystem;

    public function __construct(string $filesPath, Filesystem $fileSystem)
    {
        $this->filesPath = $filesPath;
        $this->fileSystem = $fileSystem;
    }

    public function writeResults(array $data)
    {
        return $this->processData($data);
    }

    private function processData(array $data)
    {
        $results = [];
        foreach ($data as $state => $bornData) {
            $rowResult = [];
            $rowResult[] = $state;

            foreach ($bornData as $decade => $decadeData) {
                $rowResult[] = $decadeData['borns'];
            }

            foreach ($bornData as $decade => $decadeDate) {
                $max = max($decadeData['races']);
                $key = array_search($max, $decadeData['races']);
                $rowResult[] = $key;
            }
            foreach ($bornData as $decade => $decadeDate) {
                $rowResult[] = $decadeData['male'];
                $rowResult[] = $decadeData['female'];
            }
            $results[] = $rowResult;
        }

        return $results;
    }
}
