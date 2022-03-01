# PHP HTTP Client

### Introduction
---
The PHP HTTP Client is a PHP7.x supported light weight, simple package implemented with the OOPS concepts to make the API request. It supports request to the HTTP methods `HEAD`, `GET`, `POST`, `PUT`, `PATCH`, `DELETE` &`OPTIONS`. The package also throws an Exception if the requested URL returned error or is invalid or has malformed JSON response. The package by default uses the singleton instance behavior which can be configured as per the need.

### Usage
---
The following code snippet can be used for the simple get request.
```php
require_once '../autoload.php';

use App\Http\Request;

// By default the class will have only one instance
// Pass the argument `$createNew = true` to always get the new instance
$request = Request::getInstance();
$response = $request->get('https://your-api-endpoint-url.com/');
$result = $response->getBody();
```

#### Available Methods
---
```php
// To make a GET request
$request->get('https://your-api-endpoint-url.com/');

// To make a POST request
$request->post('https://your-api-endpoint-url.com/', $payload);

// To make a PATCH request with custom headers
$request->patch('https://your-api-endpoint-url.com/resource-id', $payload, $headers);

// To make a PUT request
$request->patch('https://your-api-endpoint-url.com/resource-id', $payload);

// To make an OPTIONS request
$request->options('https://your-api-endpoint-url.com/');

// To make a DELETE request
$request->delete('https://your-api-endpoint-url.com/resource-id');
```

#### Examples
---
**Example 1**: Make a `POST` request
```php
$payload = [
    'key' => 'value',
];
$request = Request::getInstance();
$response = $request->post('https://your-api-endpoint-url.com/', $payload);
$result = $response->getBody();
```
**Example 2**: Pass custom headers
```php
$payload = [
    'key' => 'value',
];
$headers = [
    'Authorization' => 'Bearer your_auth_token'
];
$request = Request::getInstance();
$response = $request->post('https://your-api-endpoint-url.com/', $payload, $headers);
$result = $response->getBody();
```

#### Author
- [Vidhyut Pandya](https://github.com/vidhyut-simform)