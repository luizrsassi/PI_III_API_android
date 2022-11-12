<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../includes/DbOperations.php';

//$app = new \Slim\App();

//=================================
//
// Exibe o erro na tela
//
$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
//=================================                
/**
 * endpoint: createamostra
 * parameters: nomeCliente, nomeAmostra, exame, numeroContrato, concetracaoComposto, tempoExposicao, Observacao 
 * method: POST
 */
$app->post('/createamostra', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('password', 'nomeCliente', 'nomeAmostra', 'exame', 'numeroContrato', 'concetracaoComposto', 'tempoExposicao', 'Observacao'), $request, $response)) {

        $request_data = $request->getParsedBody();

        $nomeCliente = $request_data['nomeCliente'];
        $nomeAmostra = $request_data['nomeAmostra'];
        $exame = $request_data['exame'];
        $numeroContrato = $request_data['numeroContrato'];
        $concetracaoComposto = $request_data['concetracaoComposto'];
        $tempoExposicao = $request_data['tempoExposicao'];
        $Observacao = $request_data['Observacao'];
        $password = $request_data['password'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createAmostra($hash_password, $nomeCliente,  $nomeAmostra, $exame, $numeroContrato, $concetracaoComposto, $tempoExposicao, $Observacao);

        if ($result == AMOSTRA_CREATED) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Amostra adicionada com sucesso';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == AMOSTRA_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Ocorreu algum erro';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        } else if ($result == AMOSTRA_EXISTS) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'A amostra já foi registrada';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->post('/clientelogin', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('numeroContrato', 'password'), $request, $response)) {
        $request_data = $request->getParsedBody();

        $numeroContrato = $request_data['numeroContrato'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->clienteLogin($numeroContrato, $password);
        if ($result == CLIENTE_AUTHENTICATED) {
            $cliente = $db->getClienteByNumeroContrato($numeroContrato);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login realizado com sucesso';
            $response_data['cliente'] = $cliente;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == CLIENTE_NOT_FOUND) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'O cliente informado não existe';

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == CLIENTE_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Entrada inválida';

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

$app->get('/allamostras', function (Request $request, Response $response) {
    $db = new DbOperations();

    $users = $db->getAllAmostras();

    $response_data = array();

    $response_data['error'] = false;
    $response_data['users'] = $users;

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->put('/updateamostra/{id}', function (Request $request, Response $response, array $args) {

    $id = $args['id'];

    if (!haveEmptyParameters(array(
        'nomeCliente',
        'nomeAmostra',
        'exame',
        'numeroContrato',
        'concetracaoComposto',
        'tempoExposicao',
        'Observacao',
        'id'
    ), $request, $response)) {

        $request_data = $request->getParsedBody();

        $nomeCliente = $request_data['nomeCliente'];
        $nomeAmostra = $request_data['nomeAmostra'];
        $exame = $request_data['exame'];
        $numeroContrato = $request_data['numeroContrato'];
        $concetracaoComposto = $request_data['concetracaoComposto'];
        $tempoExposicao = $request_data['tempoExposicao'];
        $Observacao = $request_data['Observacao'];
        $id = $request_data['id'];

        $db = new DbOperations();

        if ($db->updateAmostra(
            $nomeCliente,
            $nomeAmostra,
            $exame,
            $numeroContrato,
            $concetracaoComposto,
            $tempoExposicao,
            $Observacao,
            $id
        )) {

            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Amostra atualizada com sucesso';
            $amostra = $db->getClienteByNumeroContrato($numeroContrato);
            $response_data['amostra'] = $amostra;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Tente novamente mais tarde';
            $amostra = $db->getClienteByNumeroContrato($numeroContrato);
            $response_data['amostra'] = $amostra;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->put(
    '/updatepassword',
    function (Request $request, Response $response) {

        if (!haveEmptyParameters(array('currentpassword', 'newpassword', 'numeroContrato'), $request, $response)) {

            $request_data = $request->getParsedBody();

            $currentpassword = $request_data['currentpassword'];
            $newpassword = $request_data['newpassword'];
            $numeroContrato = $request_data['numeroContrato'];

            $db = new DbOperations();

            $result = $db->updatePassword($currentpassword, $newpassword, $numeroContrato);

            if ($result == PASSWORD_CHANGED) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Senha atualizada';
                $response->write(json_encode($response_data));

                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if ($result == PASSWORD_DO_NOT_MATCH) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Senha incorreta';
                $response->write(json_encode($response_data));

                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if ($result == PASSWORD_NOT_CHANGED) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Ocorreu algum erro';
                $response->write(json_encode($response_data));

                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        }
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(422);
    }
);

$app->delete('/deleteamostra/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    $db = new DbOperations;

    $response_data = array();
    if ($db->deleteAmostra($id)) {
        $response_data['error'] = false;
        $response_data['message'] = 'Amostra excluída';
    } else {
        $response_data['error'] = true;
        $response_data['message'] = 'Por favor tente novamente mais tarde';
    }

    $response->write(json_encode($response_data));

    return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
});

function haveEmptyParameters($required_params, $request, $response)
{
    $error = false;
    $error_params = '';
    $request_params = $request->getParsedBody();

    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
            $error = true;
            $error_params .= $param . ', ';
        }
    }

    if ($error) {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Parâmetros necessários ' . substr($error_params, 0, -2) . ' estão faltando ou não existem.';
        $response->write(json_encode($error_detail));
    }
    return $error;
}

$app->run();
