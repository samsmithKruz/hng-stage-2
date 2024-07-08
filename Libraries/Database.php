<?php

namespace App\Libraries;

use PDO;
use PDOException;

class Database
{
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $pdo;
    private $stmt;
    private $error;
    
    public function __construct()
    {
        // Initialize database connection parameters from environment variables
        $this->host = getenv('DB_HOST');
        $this->port = getenv('DB_PORT');
        $this->dbname = getenv('DB_DATABASE');
        $this->user = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');

        try {
            // Construct DSN (Data Source Name) for connecting to PostgreSQL
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";

            // Establish a new PDO connection
            $this->pdo = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,   // Enable exceptions for errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ // Set default fetch mode to objects
            ]);
        } catch (PDOException $e) {
            // Capture and handle connection errors
            $this->error = $e->getMessage();
            die("Connection failed: " . $this->error);
        }
    }

    /**
     * Prepares an SQL statement for execution.
     *
     * @param string $sql The SQL statement to prepare.
     * @return $this
     */
    public function query($sql)
    {
        $this->stmt = $this->pdo->prepare($sql);
        return $this;
    }

    /**
     * Binds a value to a corresponding named or positional placeholder in the SQL statement.
     *
     * @param mixed $param Parameter identifier (name or position).
     * @param mixed $value Value to bind to the parameter.
     * @param int|null $type Explicit data type for the parameter.
     * @return $this
     */
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Executes a prepared statement.
     *
     * @return bool
     */
    public function execute()
    {
        return $this->stmt->execute();
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @return array
     */
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetches the next row from a result set as an object.
     *
     * @return object
     */
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Initiates a transaction.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
}

?>
