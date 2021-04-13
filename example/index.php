<?php

use Hyper\App\App;

$app = new App(autoRun : false);

// Or

$app->run();
$app->storage->user;
$app->storage->route;
$app->storage->request;