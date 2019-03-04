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
       $data = $this->processData($data);
       $fp = fopen($this->filesPath . 'results.csv', 'w');
       $fileContent = '';
       foreach ($data as $row) {
           $fileContent .= implode(',', $row) . "\n";
       }

       fwrite($fp, $fileContent);
       fclose($fp);

    }

    private function processData(array $data)
    {
        $results = [];
        foreach ($data as $state => $bornData) {
            $births = $bornData['births'];
            $rowResult = [];
            $rowResult[] = $state;

            foreach ($births as $decade => $decadeData) {
                $rowResult[] = $decadeData['borns'];
            }

            foreach ($births as $decade => $decadeData) {
                $max = max($decadeData['races']);
                $key = array_search($max, $decadeData['races']);
                $rowResult[] = $key;
            }

            $rowResult[] = $bornData['male'];
            $rowResult[] = $bornData['female'];

            $rowResult[] = number_format(round(($bornData['weight']['total'] / $bornData['weight']['numberOfBorns']) *  0.4535, 3), 3);

            $results[] = $rowResult;
        }

        return $results;
    }
}
