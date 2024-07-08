<?php

use App\Libraries\Controller;
use App\Libraries\Response;

class migratecontroller extends Controller
{
    public function __construct()
    {
        $this->model("migrate"); // Load 'migrate' model
    }

    public function index()
    {
        // Migrate users table
        $users = $this->model->migrate("users", [
            'userId' => "SERIAL PRIMARY KEY",
            'firstName' => "VARCHAR(255)",
            'lastName' => "VARCHAR(255)",
            'email' => "VARCHAR(255) UNIQUE",
            'password' => "VARCHAR(255)",
            'phone' => "VARCHAR(255)",
        ]);

        // Migrate organisation table
        $organisation = $this->model->migrate("organisation", [
            "orgId" => "SERIAL PRIMARY KEY",
            "name" => "VARCHAR(255)",
            "description" => "TEXT",
        ]);

        // Migrate organisation_owner table
        $organisation_owner = $this->model->migrate("organisation_owner", [
            "orgId" => "VARCHAR(255)",
            "userId" => "VARCHAR(255)",
        ]);

        // Migrate organisation_user table
        $organisation_user = $this->model->migrate("organisation_user", [
            "orgId" => "VARCHAR(255)",
            "userId" => "VARCHAR(255)",
        ]);

        // Check if all migrations were successful
        if ($users && $organisation && $organisation_owner && $organisation_user) {
            Response::set([
                'statusCode' => 200,
                'message' => "Migrated successfully"
            ]);
        } else {
            Response::set([
                'statusCode' => 500,
                'message' => "Migration failed"
            ]);
        }
        exit();
    }

    public function drop($tableName = "")
    {
        if (empty($tableName)) {
            Response::set([
                'statusCode' => 400,
                'message' => "Unknown model to drop."
            ]);
            exit();
        }

        $res = $this->model->dropMigration($tableName);

        if ($res) {
            Response::set([
                'statusCode' => 200,
                'message' => "Migration dropped successfully for $tableName"
            ]);
        } else {
            Response::set([
                'statusCode' => 500,
                'message' => "Failed to drop migration for $tableName"
            ]);
        }
        exit();
    }
}
