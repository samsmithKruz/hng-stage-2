<?php
// Libraries/Request.php

namespace App\Libraries;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Request extends Controller
{
    private $protected = false;
    private $requires_jwt = false;
    private $payloads = [];
    private $jwtKey; // Replace with your actual secret key

    public function __construct($protected = false, $requires_jwt = false)
    {
        $this->jwtKey = getenv('JWT_SECRET'); // Fetch JWT secret from environment
        $this->protected = $protected;
        $this->requires_jwt = $requires_jwt;
    }

    /**
     * Set additional payloads for the request.
     *
     * @param array $payloads Additional payloads to include.
     * @return Request Current instance for method chaining.
     */
    public function withPayloads($payloads)
    {
        $this->payloads = $payloads;
        return $this;
    }

    /**
     * Get the HTTP request method (GET, POST, etc.).
     *
     * @return string Request method.
     */
    public static function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Handle the request, including JWT validation if required.
     *
     * @return mixed Decoded JWT data if validated and required, true if request is handled successfully, false otherwise.
     */
    public function handle()
    {
        if ($this->protected) {
            if ($this->requires_jwt) {
                $token = $this->getBearerToken();
                if (!$token) {
                    Response::set([
                        'statusCode' => 400,
                        'message' => 'JWT token not found'
                    ]);
                    return false; // No JWT token found
                }

                $jwtData = $this->validateJWT($token); // Validate JWT token
                if ($jwtData['state'] === false) {
                    Response::set([
                        'statusCode' => 401,
                        'message' => $jwtData['data']
                    ]);
                    return false; // JWT validation failed
                }

                // Format timestamps in JWT data
                $jwtData['data']->iat = (new DateTime())->setTimestamp($jwtData['data']->iat)->format('Y-m-d H:i:s');
                $jwtData['data']->exp = (new DateTime())->setTimestamp($jwtData['data']->exp)->format('Y-m-d H:i:s');
                
                return $jwtData['data']; // Return decoded JWT data
            }
        }

        return true; // Request handled successfully
    }

    /**
     * Check if the JWT token has expired.
     *
     * @param array $payload JWT token payload.
     * @return bool True if the token has expired, false otherwise.
     */
    public function is_expired($payload)
    {
        $exp = DateTime::createFromFormat('Y-m-d H:i:s', $payload['exp']);
        return (new DateTime()) > $exp;
    }

    /**
     * Safely sanitize and retrieve data.
     *
     * @param mixed $data Data to sanitize.
     * @return mixed Sanitized data.
     */
    public static function safe_data($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return isset($data) ? $data : "";
    }

    /**
     * Generate a JWT token.
     *
     * @param array $payload Data to encode into the JWT token.
     * @return string|null JWT token if successful, null on failure.
     */
    public function generateJWT($payload)
    {
        try {
            $issuedAt = time();
            $expirationTime = $issuedAt + (3600 * getenv('JWT_EXP'));  // JWT expiration time (1 hour)

            $payload['iat'] = $issuedAt; // Issued at: time when the token was generated
            $payload['exp'] = $expirationTime; // Expiration time
            
            // Encode JWT
            $jwt = JWT::encode($payload, $this->jwtKey, 'HS256');

            return $jwt;
        } catch (\Exception $e) {
            // Handle JWT generation exception (e.g., key issue)
            return null;
        }
    }

    /**
     * Retrieve the JWT token from the Authorization header.
     *
     * @return string|null JWT token if found, null otherwise.
     */
    private function getBearerToken()
    {
        $headers = apache_request_headers();

        if (!empty($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];

            // Check if the authorization header starts with "Bearer"
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1]; // Return the token part of the header
            }
        }

        return null; // No token found
    }

    /**
     * Validate the JWT token.
     *
     * @param string $token JWT token to validate.
     * @return array Array with 'state' (true/false) and 'data' (decoded JWT data or error message).
     */
    private function validateJWT($token)
    {
        try {
            // Decode JWT token
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            return ['state' => true, 'data' => $decoded]; // Return decoded JWT data
        } catch (\Exception $e) {
            // JWT validation failed
            return ['state' => false, 'data' => $e->getMessage()];
        }
    }
}
?>
