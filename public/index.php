<?php
require '../vendor/autoload.php';
require '../config.php';

$app = new \Slim\Slim($config);

require '../src/ApISPConfig/routes.php';

$app->run();