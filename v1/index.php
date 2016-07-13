<?php

require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$user_id = NULL; // ID utilisateur - variable globale

require_once 'Routes/route_login.php';
require_once 'Routes/route_tasks.php';

$app->run();