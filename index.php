<?php

use App\Libraries\Response;

require_once __DIR__."/vendor/autoload.php";

// Load environment variables from .env
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// Set HTTP header for JSON response
header('Content-Type: application/json');

// Define the base path for your controllers
define('CONTROLLER_PATH', __DIR__ . '/Controllers/');

/**
 * Function to handle incoming HTTP requests based on the URL path.
 *
 * @param string $url The URL path after the base directory.
 */
function handleRequest($url) {
    // Sanitize the URL parameter to prevent directory traversal attacks
    // $url = preg_replace('/[^a-zA-Z0-9\/]/', '', $url);

    // Remove leading slash if present
    $url = ltrim($url, '/');

    // Separate the URL into parts based on '/'
    $parts = explode('/', $url);
    
    // Extract controller and method
    $controller = !empty($parts[0]) ? strtolower($parts[0]) . 'controller' : 'indexcontroller'; // Default to IndexController
    $method = !empty($parts[1]) ? $parts[1] : 'index'; // Default method 'index' if not specified
    $args = array_slice($parts, 2); // Arguments passed after method name

    // Construct the file path based on the controller
    $file_path = CONTROLLER_PATH . strtolower($controller) . '.php';
    
    // Check if the controller file exists
    if (file_exists($file_path)) {
        // Include the controller file
        require_once $file_path;

        // Check if the class exists
        if (class_exists($controller)) {
            $controller_instance = new $controller();

            // Check if the method exists in the controller
            if (method_exists($controller_instance, $method)) {
                // Call the method with arguments and capture the result
                call_user_func_array([$controller_instance, $method], $args);

            } else {
                // Method not found error
                http_response_code(404);
                echo json_encode(['error' => 'Path not found']);
            }
        } else {
            // Controller not found error
            http_response_code(404);
            echo json_encode(['error' => 'Route not found']);
        }
    } else {
        // Controller file not found error
        Response::set([
            'statusCode' => 404,
            'message' => "Endpoint not found"
        ]);
        exit();
    }
}

// Extract the requested URL path from $_SERVER['REQUEST_URI']
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = urldecode($request_uri);

// Remove the base directory from the request URI, if applicable
$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($base_dir !== '/') {
    $request_uri = preg_replace('#^' . preg_quote($base_dir) . '#', '', $request_uri);
}

// Handle the request
handleRequest($request_uri);
?>
