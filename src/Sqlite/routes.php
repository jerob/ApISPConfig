<?php
$app->get('/', function () use ($app) {
    $app->render('index.phtml', array('basePath'=>$app->config('base.path')));
});