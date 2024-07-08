<?php

use App\Libraries\Controller;
use App\Libraries\Request;
use App\Libraries\Response;

class apicontroller extends Controller
{
    private Request $requestHandler;
    private $payload;

    public function __construct()
    {
        $this->requestHandler = new Request(true, true); // Initialize Request handler with protection and JWT requirement
        $this->payload = $this->requestHandler->handle(); // Retrieve and handle JWT payload
        if (!$this->payload) {
            exit(); // Exit if JWT validation fails
        }
        $this->model("api"); // Load 'api' model
    }

    public function index()
    {
        exit(); // Placeholder method, exits immediately
    }

    public function users($id = "")
    {
        if (empty($id) || !isset($id)) {
            Response::set([
                'statusCode' => 400,
                'status' => "Bad Request",
                'message' => 'Client error'
            ]);
            exit();
        }

        // Get user record based on user ID and payload user ID
        $user = $this->model->getUserRecord($this->payload->userId, $id);

        if (!$user) {
            Response::set([
                'statusCode' => 400,
                'status' => "Bad Request",
                'message' => 'Client error'
            ]);
            exit();
        }

        // Format and output user data as JSON
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "User fetched successfully",
            "data" => [
                "userId" => $user->userid,
                "firstName" => $user->firstname,
                "lastName" => $user->lastname,
                "email" => $user->email,
                "phone" => $user->phone,
            ]
        ]);
        exit();
    }

    public function organisations($orgId = "", $usersRoute = "")
    {
        // Handling POST requests
        if (Request::getMethod() == 'POST') {
            if (!empty($orgId) && !empty($usersRoute) && $usersRoute == 'users') {
                // Add users to an organization
                $rawData = json_decode(file_get_contents("php://input"), true);
                $res = $this->model->addUsersToOrganisation($orgId, $rawData);

                if (!$res) {
                    Response::set([
                        'statusCode' => 400,
                        'status' => "Bad Request",
                        'message' => 'Client error'
                    ]);
                    exit();
                }

                // Successful response
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "User added to organization"
                ]);
                exit();
            }

            // Create a new organization
            $rawData = json_decode(file_get_contents("php://input"), true);
            $res = $this->model->createOrganisation($this->payload->userId, $rawData);

            if (!$res) {
                Response::set([
                    'statusCode' => 400,
                    'status' => "Bad Request",
                    'message' => 'Client error'
                ]);
                exit();
            }

            // Successful response
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Organisation created successfully",
                "data" => (object) [
                    'orgId' => $res->orgId,
                    'name' => $res->name,
                    'description' => $res->description,
                ]
            ]);
            exit();
        }

        // Handling GET requests

        // Retrieve specific organization by ID
        if (!empty($orgId)) {
            $organisation = $this->model->getOrganisations($orgId);

            if (!$organisation) {
                Response::set([
                    'statusCode' => 400,
                    'status' => "Bad Request",
                    'message' => 'Client error'
                ]);
                exit();
            }

            // Successful response
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Organisation fetched successfully",
                "data" => (object) [
                    'orgId' => $organisation->orgid,
                    'name' => $organisation->name,
                    'description' => $organisation->description,
                ]
            ]);
            exit();
        }

        // Retrieve all organizations associated with the current user
        $organisations = $this->model->getAllOrganisations($this->payload->userId);

        if (!$organisations) {
            Response::set([
                'statusCode' => 400,
                'status' => "Bad Request",
                'message' => 'Client error'
            ]);
            exit();
        }

        // Format and output organizations data as JSON
        $data = [];
        foreach ($organisations as $org) {
            $data[] = [
                "orgId" => $org->orgid,
                "name" => $org->name,
                "description" => $org->description,
            ];
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Organisations fetched successfully",
            "data" => $data
        ]);
        exit();
    }
}
