<?php
$app->get('/', function () use ($app) {
    $app->render('index.phtml',array('basePath'=>$app->config('base.path')));
});
$app->get('/thanks', function () use ($app) {
	$result = array();
	if (isset($_POST['accept']) and !empty($_POST['email']) and !empty($_POST['username'])) {
		// $this->ispconfig();
	}
	$app->render('thanks.phtml',array('email'=>''));
});
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});