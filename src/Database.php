<?php
declare(strict_types=1);

final class Database
{
    private string $host;
    private string $username;
    private string $password;
    private string $databaseName;
    private ?mysqli $connection;

    public function __construct(string $host, string $username, string $password, string $databaseName)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->databaseName = $databaseName;
        $this->connection = null;
    }

    public function connect(): mysqli
    {
        if ($this->connection instanceof mysqli) {
            return $this->connection;
        }

        $connection = @new mysqli($this->host, $this->username, $this->password, $this->databaseName);
        if ($connection->connect_error) {
            throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
        }
        if (!$connection->set_charset('utf8mb4')) {
            throw new RuntimeException('Failed to set charset: ' . $connection->error);
        }

        $this->connection = $connection;
        return $this->connection;
    }

    public function prepare(string $sql): mysqli_stmt
    {
        $stmt = $this->connect()->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare statement');
        }

        return $stmt;
    }

    public function disconnect(): void
    {
        if ($this->connection instanceof mysqli) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}
