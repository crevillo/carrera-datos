<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 2019-03-03
 * Time: 20:00
 */

require './vendor/autoload.php';

$fileSystem = new \Symfony\Component\Filesystem\Filesystem();

$filesPath = getenv("HOME") . "/data-race/";

$downloader = new App\Downloader(
    new \Google\Cloud\Storage\StorageClient([
        'keyFilePath' => './gcloud-account.json',
        'projectId' => 'tce-sistemas-14702046268333'
    ]),
    $fileSystem,
    $filesPath
);

$downloader->downloadSourceFiles();

$reader = new \App\Reader(
    $filesPath,
    $fileSystem
);

$start = microtime(true);
$reader->execute();
$time_elapsed_secs = microtime(true) - $start;




