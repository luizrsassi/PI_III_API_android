<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../includes/DbConnect.php';

$app = new \Slim\App();

//=================================
//
// Exibe o erro na tela
//
//$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
//=================================
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    $db = new DbConnect;

    if ($db->connect() != null) {
        echo 'Connection Succesful!';
    };

    return $response;
});

$app->run();
