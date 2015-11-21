<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$c = new \Slim\Container(require 'app/config/settings.php');
$c->register(new \Mylab\View\PhptalProvider);

$app = new Slim\App($c);

$app->get('/', function ($request, $response, $args) {
    $this->view->aaa = 123;
    $this->view->assign('bbb', 'a');
    return $this->view->render($response, 'test.xhtml');
});

$app->get('/test', function ($request, $response, $args) {
    $this->view->test = 1;
    return $response->withJson($this->view->getIterator());
});

$app->run();
