<?php
$headers = apache_request_headers();
if (array_key_exists('TEST_ENV', $headers) && $headers['TEST_ENV'] == 'true') {
    putenv("TEST_ENV=true");
} else {
    putenv("TEST_ENV=false");
}

session_id(bin2hex(random_bytes(16)));

define("PROJECT_ROOT_PATH", __DIR__ . "/../");

// include core dependencies 
require_once PROJECT_ROOT_PATH . "/startup/config.php";
require_once PROJECT_ROOT_PATH . "/startup/logger.php";
require_once PROJECT_ROOT_PATH . "/routes/BaseController.php";
require_once PROJECT_ROOT_PATH . "/models/Database.php";

// include models and controllers as per db table structure 
require_once PROJECT_ROOT_PATH . "/routes/UserController.php";
require_once PROJECT_ROOT_PATH . "/models/UserModel.php";
?>