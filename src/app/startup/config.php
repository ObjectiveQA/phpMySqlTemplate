<?php
if (file_exists('../configProd.json')) {
    $jsonConfig = file_get_contents('../configProd.json');
} else {
    $jsonConfig = file_get_contents('../configDev.json');
}

$config = json_decode($jsonConfig);

if (getenv('AUTH_KEY') != $config->authKey) {
    header("HTTP/1.1 401 Unauthorized");
    echo 'Unauthorized.';
    exit;
}

define("DB_HOST", $config->db->host);
define("DB_USERNAME", $config->db->username);
define("DB_PASSWORD", $config->db->password);
define("DB_DATABASE_NAME", $config->db->databaseName);
define("APP_ENV", $config->appEnv);
?>