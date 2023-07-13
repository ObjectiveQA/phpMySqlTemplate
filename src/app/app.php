<?php
try {
    require __DIR__ . "/startup/bootstrap.php";

    $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uriElements = explode( '/', $uri );
    $requestedRoute = $uriElements[4];

    if (!isset($requestedRoute) || $requestedRoute == '') {
        header("HTTP/1.1 400 Bad Request");
        echo 'No path specified.';
        exit;
    }

    switch ($requestedRoute) {
        case 'users':
            $controller = new UserController();
            break;
        default:
            header("HTTP/1.1 404 Not Found");
            echo 'No route matched the requested path.';
            exit;
    }

    $controller->{$requestMethod}(array_slice($uriElements, 5));
} catch (Throwable $e) {
    error_log($e);

    $data = json_encode(array('error' => 'Something went wrong! Please contact support.'));
    $httpHeaders = array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error');
    header_remove('Set-Cookie');
    foreach ($httpHeaders as $httpHeader) {
        header($httpHeader);
    }
    echo $data;
    exit;
}
?>
