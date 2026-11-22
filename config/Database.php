<?php

class Database
{
    private string $host = 'localhost';
    private string $port = '5432';
    private string $dbname = 'online_store';
    private string $username = 'postgres';

    // Replace with your actual local PostgreSQL password.
    private string $password = 'jayson222000';

    public function connect(): PDO
    {
        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";

        return new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
