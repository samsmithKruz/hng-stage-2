<?php

use App\Libraries\Database;
use App\Libraries\Request;

class api extends Database
{
    private $db;

    public function __construct()
    {
        // Initialize a new instance of the Database class
        $this->db = new Database;
    }

    /**
     * Fetches user record based on user ID and organization ID.
     *
     * @param string $myId The ID of the organization.
     * @param string $id The ID of the user.
     * @return array|null The fetched user record or null if not found.
     */
    public function getUserRecord($myId, $id)
    {
        return $this->db->query("
            SELECT 
                users.*
            FROM 
                users
            LEFT JOIN 
                organisation_user ON organisation_user.userid::VARCHAR = users.userid::VARCHAR
            LEFT JOIN 
                organisation ON organisation.orgid::VARCHAR = organisation_user.orgid::VARCHAR
            WHERE
                users.userid::VARCHAR = :id and organisation_user.orgid::VARCHAR = :myid
        ")
        ->bind(":id", $id)
        ->bind(":myid", $myId)
        ->single(); // Fetches a single row
    }

    /**
     * Fetches all organizations associated with a user.
     *
     * @param string $id The ID of the user.
     * @return array An array of organizations associated with the user.
     */
    public function getAllOrganisations($id)
    {
        return $this->db->query("
            SELECT 
                organisation.orgid::VARCHAR,
                organisation.name::VARCHAR,
                organisation.description::VARCHAR
            FROM organisation
            LEFT JOIN organisation_user ON
                organisation_user.orgid::VARCHAR = organisation.orgid::VARCHAR
            WHERE 
                organisation_user.userid::VARCHAR = :id
        ")
        ->bind(":id", Request::safe_data($id)) // Sanitizes input
        ->resultSet(); // Fetches multiple rows
    }

    /**
     * Fetches a single organization by its ID.
     *
     * @param string $id The ID of the organization.
     * @return array|null The fetched organization or null if not found.
     */
    public function getOrganisations($id)
    {
        return $this->db->query("
            SELECT 
                organisation.orgid::VARCHAR,
                organisation.name::VARCHAR,
                organisation.description::VARCHAR
            FROM organisation
            WHERE 
                organisation.orgid::VARCHAR = :id
        ")
        ->bind(":id", Request::safe_data($id)) // Sanitizes input
        ->single(); // Fetches a single row
    }

    /**
     * Creates a new organization.
     *
     * @param string $id The ID of the user creating the organization.
     * @param object $data An object containing 'name' and 'description' of the organization.
     * @return object|bool An object with 'orgId', 'name', and 'description' if successful, otherwise false.
     */
    public function createOrganisation($id, $data)
    {
        extract((array)$data); // Extracts 'name' and 'description' from $data object

        // Check if 'name' is empty or not set
        if (empty($name) || !isset($name)) {
            return false;
        }

        // Check if organization with the same name already exists
        $this->db->query("
            SELECT orgid FROM organisation WHERE name::VARCHAR = :name
        ")
        ->bind(":name", Request::safe_data($name)) // Sanitizes input
        ->execute(); // Executes the query

        // If organization with the same name exists, return false
        if ($this->db->rowCount() > 0) {
            return false;
        }

        // Create organization in the database
        $this->db->query("
            INSERT INTO organisation(name, description) VALUES (:name, :description)
        ")
        ->bind(":name", $name)
        ->bind(":description", $description)
        ->execute(); // Executes the query

        // If no rows were affected, return false
        if ($this->db->rowCount() == 0) {
            return false;
        }

        // Get the last inserted ID (orgId)
        $orgId = $this->db->lastInsertId();

        // Create organization owner record
        $this->db->query("
            INSERT INTO organisation_owner(orgid, userid) VALUES (:orgid, :userid)
        ")
        ->bind(":orgid", $orgId)
        ->bind(":userid", $id)
        ->execute(); // Executes the query

        // If no rows were affected, return false
        if ($this->db->rowCount() == 0) {
            return false;
        }

        // Add user to organization
        $this->db->query("
            INSERT INTO organisation_user(orgid, userid) VALUES (:id, :user)
        ")
        ->bind(":id", $orgId)
        ->bind(":user", $id)
        ->execute(); // Executes the query

        // If no rows were affected, return false
        if ($this->db->rowCount() == 0) {
            return false;
        }

        // Return an object with organization details
        return (object)['orgId' => $orgId, 'name' => $name, 'description' => $description];
    }

    /**
     * Adds a user to an organization.
     *
     * @param string $id The ID of the organization.
     * @param array $data An array containing 'userId' to be added to the organization.
     * @return bool True if user was added successfully, false otherwise.
     */
    public function addUsersToOrganisation($id, $data)
    {
        // Check if $id or 'userId' is empty or not set
        if (empty($id) || !isset($data['userId'])) {
            return false;
        }

        // Check if user is already added to the organization
        $this->db->query("
            SELECT orgid FROM organisation_user WHERE orgid = :id AND userid = :user
        ")
        ->bind(":id", $id)
        ->bind(":user", $data['userId'])
        ->execute(); // Executes the query

        // If user is not already added to the organization
        if ($this->db->rowCount() == 0) {
            // Add user to organization
            $this->db->query("
                INSERT INTO organisation_user(orgid, userid) VALUES (:id, :user)
            ")
            ->bind(":id", $id)
            ->bind(":user", $data['userId'])
            ->execute(); // Executes the query

            // If no rows were affected, return false
            if ($this->db->rowCount() == 0) {
                return false;
            }
            return true; // Return true if user was added successfully
        }
        return false; // Return false if user is already added to the organization
    }
}
