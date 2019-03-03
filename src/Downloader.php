<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 2019-03-03
 * Time: 21:45
 */

namespace App;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Symfony\Component\Filesystem\Filesystem;

class Downloader
{
    private $storageClient;

    private $fileSystem;

    private $destination;

    public function __construct(StorageClient $storageClient, Filesystem $fileSystem, string $destination)
    {
        $this->storageClient = $storageClient;
        $this->fileSystem = $fileSystem;
        $this->destination = $destination;
    }

    public function downloadSourceFiles($forceDownload = false)
    {
        if (!$this->fileSystem->exists($this->destination)) {
            $this->fileSystem->mkdir($this->destination);
            $forceDownload = true;
        }

        if ($forceDownload) {
            $this->downloadFiles();
        }
    }

    private function downloadFiles()
    {
        $bucket = $this->storageClient->bucket('securitas');

        /** @var StorageObject $object */
        foreach ($bucket->objects() as $object) {
            $object->downloadToFile($this->destination . $object->name());
        }
    }
}
