<?php
// Consume headers
$headers = apache_request_headers();

if (array_key_exists('AUTH_KEY', $headers)) {
    $authKey = $headers['AUTH_KEY'];
    putenv("AUTH_KEY=$authKey");
} else {
    putenv("AUTH_KEY=");
}

if (array_key_exists('TEST_ENV', $headers) && $headers['TEST_ENV'] == 'true') {
    putenv("TEST_ENV=true");
} else {
    putenv("TEST_ENV=false");
}

// Set session id
session_id(bin2hex(random_bytes(16)));

// Define project root
define("PROJECT_ROOT_PATH", __DIR__ . "/../");

// include core dependencies 
require_once PROJECT_ROOT_PATH . "/startup/config.php";
require_once PROJECT_ROOT_PATH . "/routes/BaseController.php";
require_once PROJECT_ROOT_PATH . "/models/Database.php";

// include models and controllers as per db table structure 
require_once PROJECT_ROOT_PATH . "/routes/UserController.php";
require_once PROJECT_ROOT_PATH . "/models/UserModel.php";
?>