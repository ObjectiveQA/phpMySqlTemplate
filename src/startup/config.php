<?php
$configFilename = 'config.json';

if (file_exists('../config.json')) {
    // config.json stored in project root for local development
    $jsonConfig = file_get_contents('../config.json');
} else {
    // config.json stored outside of public_html dir in online hosting
    $jsonConfig = file_get_contents('../../configPhpMySqlTemplate.json');
}

$config = json_decode($jsonConfig);

define("DB_HOST", $config->db->host);
define("DB_USERNAME", $config->db->username);
define("DB_PASSWORD", $config->db->password);
define("DB_DATABASE_NAME", $config->db->databaseName);
define("APP_ENV", $config->appEnv);
?>