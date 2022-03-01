
<?php
// Just a quick sample usage of the HTTP Request client
require_once '../autoload.php';

use Exception;
use App\Http\Request;

if (empty($_POST['url']) || empty($_POST['method'])) {
    throw new Exception('Invalid request');
}

$method = $_POST['method'];

$allowedMethods = [
    'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'
];

if (!in_array($method, $allowedMethods)) {
    throw new Exception('Invalid request method');
}

$body = !empty($_POST['body']) ? json_decode($_POST['body'], true) : [];
$headers = !empty($_POST['headers']) ? json_decode($_POST['headers'], true) : [];

try {
    $request = Request::getInstance();
    $response = $request->{strtolower($method)}($_POST['url'], $body, $headers);

    header('Content-type: application/json');

    echo json_encode([
        'headers' => $response->getHeaders(),
        'response' => $response->getBody(),
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode());
    header('Content-type: application/json');
    echo json_encode($e->getMessage());
}
