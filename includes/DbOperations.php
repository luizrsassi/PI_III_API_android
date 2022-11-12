<?php

class DbOperations
{
    private $con;

    function __construct()
    {
        require_once __DIR__ . '/DbConnect.php';
        $db = new DbConnect;
        $this->con = $db->connect();
    }


    public function createAmostra(
        $password,
        $nomeCliente,
        $nomeAmostra,
        $exame,
        $numeroContrato,
        $concetracaoComposto,
        $tempoExposicao,
        $Observacao
    ) {
        if (!$this->isClienteExist($numeroContrato)) {
            $stmt = $this->con->prepare("INSERT INTO amostras (password, nomeCliente, nomeAmostra, exame, numeroContrato, 
            concetracaoComposto, tempoExposicao, Observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $password, $nomeCliente, $nomeAmostra, $exame, $numeroContrato, $concetracaoComposto, $tempoExposicao, $Observacao);
            if ($stmt->execute()) {
                return AMOSTRA_CREATED;
            } else {
                return AMOSTRA_FAILURE;
            }
        }
        return AMOSTRA_EXISTS;
    }

    public function clienteLogin($numeroContrato, $password)
    {
        if ($this->isClienteExist($numeroContrato)) {
            $hashed_password = $this->getClientesPasswordByNumeroContrato($numeroContrato);
            if (password_verify($password, $hashed_password)) {
                return CLIENTE_AUTHENTICATED;
            } else {
                return CLIENTE_PASSWORD_DO_NOT_MATCH;
            }
        } else {
            return CLIENTE_NOT_FOUND;
        }
    }

    private function getClientesPasswordByNumeroContrato($numeroContrato)
    {
        $stmt = $this->con->prepare("SELECT 
        password 
        FROM amostras 
        WHERE numeroContrato = ?");

        $stmt->bind_param("s", $numeroContrato);
        $stmt->execute();
        $stmt->bind_result($password);

        $stmt->fetch();
        return $password;
    }

    public function getAllAmostras()
    {
        $stmt = $this->con->prepare("SELECT 
        id, 
        nomeCliente, 
        nomeAmostra, 
        exame, 
        numeroContrato, 
        concetracaoComposto, 
        tempoExposicao, 
        Observacao 
        FROM amostras;");

        $stmt->execute();
        $stmt->bind_result(
            $id,
            $nomeCliente,
            $nomeAmostra,
            $exame,
            $numeroContrato,
            $concetracaoComposto,
            $tempoExposicao,
            $Observacao
        );

        $amostras = array();
        while ($stmt->fetch()) {
            $amostra = array();
            $amostra['id'] = $id;
            $amostra['nomeCliente'] = $nomeCliente;
            $amostra['nomeAmostra'] = $nomeAmostra;
            $amostra['exame'] = $exame;
            $amostra['numeroContrato'] = $numeroContrato;
            $amostra['concetracaoComposto'] = $concetracaoComposto;
            $amostra['tempoExposicao'] = $tempoExposicao;
            $amostra['Observacao'] = $Observacao;

            array_push($amostras, $amostra);
        }
        return $amostras;
    }

    public function getClienteByNumeroContrato($numeroContrato)
    {
        $stmt = $this->con->prepare("SELECT 
        id, 
        nomeCliente, 
        nomeAmostra, 
        exame, 
        numeroContrato, 
        concetracaoComposto, 
        tempoExposicao, 
        Observacao 
        FROM amostras 
        WHERE numeroContrato = ?");

        $stmt->bind_param("s", $numeroContrato);
        $stmt->execute();
        $stmt->bind_result(
            $id,
            $nomeCliente,
            $nomeAmostra,
            $exame,
            $numeroContrato,
            $concetracaoComposto,
            $tempoExposicao,
            $Observacao
        );

        $stmt->fetch();
        $amostra = array();
        $amostra['id'] = $id;
        $amostra['nomeCliente'] = $nomeCliente;
        $amostra['nomeAmostra'] = $nomeAmostra;
        $amostra['exame'] = $exame;
        $amostra['numeroContrato'] = $numeroContrato;
        $amostra['concetracaoComposto'] = $concetracaoComposto;
        $amostra['tempoExposicao'] = $tempoExposicao;
        $amostra['Observacao'] = $Observacao;

        return $amostra;
    }

    public function updateAmostra($nomeCliente, $nomeAmostra, $exame, $numeroContrato, $concetracaoComposto, $tempoExposicao, $Observacao, $id)
    {
        $stmt = $this->con->prepare('UPDATE amostras SET nomeCliente = ?, nomeAmostra = ?, exame = ?, numeroContrato = ?, concetracaoComposto = ?, tempoExposicao = ?, Observacao = ? WHERE id = ?;');
        $stmt->bind_param('sssssssi', $nomeCliente, $nomeAmostra, $exame, $numeroContrato, $concetracaoComposto, $tempoExposicao, $Observacao, $id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePassword($currentpassword, $newpassword, $numeroContrato)
    {
        $hashed_password = $this->getClientesPasswordByNumeroContrato($numeroContrato);

        if (password_verify($currentpassword, $hashed_password)) {

            $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);

            $stmt = $this->con->prepare("UPDATE amostras SET password = ? WHERE numeroContrato = ?;");
            $stmt->bind_param('ss', $hash_password, $numeroContrato);

            if ($stmt->execute())
                return PASSWORD_CHANGED;
            return PASSWORD_NOT_CHANGED;
        } else {
            return PASSWORD_DO_NOT_MATCH;
        }
    }

    public function deleteAmostra($id)
    {
        $stmt = $this->con->prepare("DELETE FROM amostras WHERE id = ?;");
        $stmt->bind_param("i", $id);

        if ($stmt->execute())
            return true;
        return false;
    }

    private function isClienteExist($numeroContrato)
    {
        $stmt = $this->con->prepare("SELECT id 
            FROM amostras 
            WHERE numeroContrato = ?");

        $stmt->bind_param("s", $numeroContrato);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }
}
