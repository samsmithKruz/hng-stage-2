<?php

use App\Libraries\Database;
use App\Libraries\Migration;

class migrate extends Database
{
    private $db;

    public function __construct()
    {
        // Initialize a new instance of the Database class
        $this->db = new Database;
    }

    /**
     * Executes the migration for a specific table with a given schema.
     *
     * @param string $table Name of the table to migrate.
     * @param array $schema Schema definition for the table.
     * @return bool True if migration is successful, false otherwise.
     */
    public function migrate($table, $schema)
    {
        $model = new Migration($table);
        $model->schema = $schema;
        return $model->migrate();
    }

    /**
     * Drops a specified table from the database.
     *
     * @param string $table Name of the table to drop.
     * @return bool True if table is dropped successfully, false otherwise.
     */
    public function dropMigration($table)
    {
        $model = new Migration($table);
        return $model->dropTable();
    }
}
