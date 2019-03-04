<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 2019-03-03
 * Time: 20:00
 */

require './vendor/autoload.php';

use App\ProcessCommand;

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ProcessCommand());
$application->run();







