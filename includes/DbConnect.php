<?php

class DbConnect
{

    private $con;

    function connect()
    {
        include_once __DIR__ . '/Constants.php';

        $this->con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if (mysqli_connect_errno()) {
            echo "Falha de conexão " . mysqli_connect_error();
            return null;
        }

        return $this->con;
    }
}
