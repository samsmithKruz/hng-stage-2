<?php

use App\Libraries\Database;
use App\Libraries\Request;
use App\Libraries\Response;

class main extends Database
{
    private $db;
    private Request $requestHandler;

    public function __construct()
    {
        // Initialize a new instance of the Database class
        $this->db = new Database;
    }

    /**
     * Validates input fields for registration.
     *
     * @param array $arr Associative array of input fields and values.
     * @return array Array of validation errors.
     */
    private function inputValidate($arr)
    {
        $errors = [];
        foreach ($arr as $key => $e) {
            if (empty($e) || !isset($e)) {
                array_push($errors, ["field" => "$key", "message" => "$key is invalid"]);
            }
        }
        return $errors;
    }

    /**
     * Registers a new user.
     *
     * @param object $data An object containing user registration data.
     * @return bool True if registration is successful, false otherwise.
     */
    public function register($data)
    {
        extract((array)$data); // Extracts data object into variables

        $errors = [];

        // Validate email field
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, ["field" => "email", "message" => "email is invalid"]);
        }

        // Validate phone field
        if (empty($phone) || !isset($phone)) {
            array_push($errors, ["field" => "phone", "message" => "phone number is invalid"]);
        }

        // Validate other input fields using inputValidate method
        $errors = array_merge($errors, $this->inputValidate(["firstName" => @$firstName, "lastName" => @$lastName, "password" => @$password]));

        // If there are validation errors, return false
        if (count($errors) > 0) {
            http_response_code(422);
            echo json_encode([
                "errors" => $errors
            ]);
            return false;
        }

        // Check if user already registered
        $this->db->query("SELECT firstname from users where email=:email")
            ->bind(":email", $email)
            ->execute();

        // If email already exists, return error response
        if ($this->db->rowCount() > 0) {
            http_response_code(422);
            echo json_encode([
                "errors" => ["field" => "email", "message" => "Email already exists"]
            ]);
            return false;
        }

        // Register new user
        $this->db->query("INSERT INTO users(firstname,lastname,email,password,phone) values(:firstname,:lastname,:email,:password,:phone)")
            ->bind(":firstname", $firstName)
            ->bind(":lastname", $lastName)
            ->bind(":email", $email)
            ->bind(":phone", $phone)
            ->bind(":password", hash('md5', $password)) // Hash password before storing
            ->execute();

        // If registration query fails, return error response
        if ($this->db->rowCount() == 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "Bad request",
                "message" => "Registration unsuccessful",
                "statusCode" => 400
            ]);
            return false;
        }

        // Retrieve newly registered user's ID
        $userId = $this->db->lastInsertId();

        // Generate JWT token for authentication
        $this->requestHandler = new Request(false, false);
        $jwt = $this->requestHandler->generateJWT([
            'userId' => $userId
        ]);

        // Create organization for the user
        $this->db->query("INSERT INTO organisation(name,description) values(:name,:description)")
            ->bind(":name", "$firstName's Organisation")
            ->bind(":description", "description about this organisation")
            ->execute();

        // Retrieve newly created organization's ID
        $orgId = $this->db->lastInsertId();

        // Assign user as the owner of the organization
        $this->db->query("INSERT INTO organisation_owner(orgid,userid) values(:orgid,:userid)")
            ->bind(":orgid", $orgId)
            ->bind(":userid", $userId)
            ->execute();

        // Add user to the organization
        $this->db->query("INSERT INTO organisation_user(orgid,userid) values(:id,:user)")
            ->bind(":id", $userId)
            ->bind(":user", $userId)
            ->execute();

        // Return success response with JWT token and user data
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful",
            "data" => [
                "accessToken" => $jwt,
                "user" => [
                    "userId" => $userId,
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "email" => $email,
                    "phone" => $phone,
                ]
            ]
        ]);

        return true;
    }

    /**
     * Logs in a user.
     *
     * @param object $data An object containing user login data.
     * @return bool True if login is successful, false otherwise.
     */
    public function login($data)
    {
        extract((array)$data); // Extracts data object into variables

        // Validate email and password fields
        if (empty($email) || !isset($email) || empty($password) || !isset($password)) {
            Response::set([
                "statusCode" => 401,
                "message" => "Authentication failed",
                "status" => "Bad request"
            ]);
            return false;
        }

        // Check if user exists with provided credentials
        $user = $this->db->query("SELECT *, NULL as password from users where email=:email and password=:password")
            ->bind(":email", $email)
            ->bind(":password", hash('md5', $password)) // Hash password for comparison
            ->single();

        // If user found, generate JWT token for authentication
        if ($this->db->rowCount() > 0) {
            $this->requestHandler = new Request(false, false);
            $jwt = $this->requestHandler->generateJWT([
                'userId' => $user->userid
            ]);

            // Return success response with JWT token and user data
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "data" => [
                    "accessToken" => $jwt,
                    "user" => [
                        "userId" => $user->userid,
                        "firstName" => $user->firstname,
                        "lastName" => $user->lastname,
                        "email" => $user->email,
                        "phone" => $user->phone,
                    ]
                ]
            ]);
            return true;
        }

        // Return error response if user not found or login fails
        http_response_code(400);
        echo json_encode([
            "status" => "Bad request",
            "message" => "Login unsuccessful",
            "statusCode" => 400
        ]);
        return false;
    }
}
