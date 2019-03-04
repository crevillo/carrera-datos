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

    public function __construct(string $filesDir, Filesystem $fileSystem)
    {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
    }

    private function fileGetContentsChunked($file, $chunkSize, $callback)
    {
        try {
            $handle = fopen($file, "r");
            $i = 0;
            while (!feof($handle))
            {
                call_user_func_array($callback, [fread($handle, $chunkSize), &$handle, $i]);
                $i++;
            }

            fclose($handle);

        } catch(\Exception $e) {
            trigger_error("fileGetContentsChunked::" . $e->getMessage(),E_USER_NOTICE);
            return false;
        }

        return true;
    }

    private function readNatalityFile($file)
    {
        $handle = fopen($this->filesDir . $file, 'r');
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        }
    }

    public function execute()
    {

        $files = scandir($this->filesDir);

        foreach ($files as $file) {
            if (strpos($file, 'natalidad') !== false) {
                $this->readNatalityFile($file);
            }
        }
    }
}
