<?php

namespace App\Libraries;

class Response {
    /**
     * Set HTTP status code and send JSON response.
     *
     * @param array $array Response data including 'statusCode' and optional other data.
     */
    public static function set($array) {
        // Set HTTP status code (default to 500 if statusCode is not provided)
        http_response_code($array['statusCode'] ?? 500);

        // Output response as JSON
        echo json_encode($array);
    }
}

?>
