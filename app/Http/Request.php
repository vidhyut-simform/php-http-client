<?php

namespace App\Http;

use Exception;
use App\Http\Response;

/**
 * Class Request is to make the HTTP Request
 *
 * Throws the exception if the request has failed
 * Returns instance of the Response class on successful execution
 *
 * Example Usage:
 * $request = Request::getInstance();
 * $response = $request->get('https://myapiendpoint.com');
 * $result = $response->getBody();
 *
 * @author Vidhyut Pandya <vidhyut.p@simformsolutions.com>
 */
class Request
{
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * Instance of the class Request
     *
     * @var self
     */
    private static $instance;

    /**
     * Send DELETE request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function delete(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_DELETE, $url, $body, $headers);
    }

    /**
     * Send GET request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function get(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_GET, $url, $body, $headers);
    }

    /**
     * Send HEAD request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function head(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_HEAD, $url, $body, $headers);
    }

    /**
     * Send OPTIONS request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function options(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_OPTIONS, $url, $body, $headers);
    }

    /**
     * Send PATCH request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function patch(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_PATCH, $url, $body, $headers);
    }

    /**
     * Send POST request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function post(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_POST, $url, $body, $headers);
    }

    /**
     * Send PUT request.
     *
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function put(string $url, array $body = [], array $headers = [])
    {
        return $this->send(self::METHOD_PUT, $url, $body, $headers);
    }

    /**
     * Build structure for the HTTP Request.
     *
     * @param string $method Method (POST, PATH, DELETE etc.)
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return array
     */
    protected function buildStructure(string $method, array $body = [], array $headers = [])
    {
        $payload = '';
        $method = strtoupper($method);
        // Change the case of all keys
        $headers = array_change_key_case($headers, CASE_LOWER);

        switch ($method) {
            case self::METHOD_DELETE:
            case self::METHOD_POST:
            case self::METHOD_PUT:
            case self::METHOD_PATCH:
                if (is_array($body)) {
                    if (!empty($headers['content-type'])) {
                        switch (trim($headers['content-type'])) {
                            case 'application/x-www-form-urlencoded':
                                $body = http_build_query($body);
                                break;
                            case 'application/json':
                                $body = json_encode($body);
                                break;
                        }
                    } else {
                        $headers['content-type'] = 'application/json';
                        $body = json_encode($body);
                    }
                } elseif (empty($headers['content-type'])) {
                    $headers['content-type'] = 'application/json';
                    $body = json_encode($body);
                }

                $payload = $body;
                break;
        }

        $structure = [
            'http' => [
                'method' => $method,
            ],
        ];

        // Prepare and set headers from the key value pair
        if ($headers) {
            $structure['http']['header'] = implode(
                "\r\n",
                array_map(
                    function ($value, $key) {
                        return sprintf("%s: %s", $key, $value);
                    },
                    $headers,
                    array_keys($headers)
                )
            );
        }

        // Add payload if required
        if ($payload) {
            $structure['http']['content'] = $payload;
        }

        return $structure;
    }

    /**
     * Build Request URL
     *
     * @param string $method Method (GET, POST, etc.)
     * @param string $url
     * @param null|array $body
     * @return string
     */
    protected function buildURL(string $method, string $url, array $body = [])
    {
        $method = strtoupper($method);

        switch ($method) {
            case self::METHOD_HEAD:
            case self::METHOD_OPTIONS:
            case self::METHOD_GET:
                if (is_array($body)) {
                    // Append the query param if the URL already has a query parameter
                    if (strpos($url, '?') !== false) {
                        $url .= '&';
                    } else {
                        $url .= '?';
                    }

                    $url .= urldecode(http_build_query($body));
                }
                break;
        }

        return $url;
    }

    /**
     * Sends HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    private function send(string $method, string $url, array $body = [], array $headers = [])
    {
        // Prepare request URL
        $url = $this->buildURL($method, $url, $body);
        // Prepare request structure including payload and request headers
        $structure = $this->buildStructure($method, $body, $headers);

        // Create stream context
        $streamContext = stream_context_create($structure);

        // Make final request with parameters
        $response = file_get_contents($url, false, $streamContext);

        if ($response === false) {
            $statusLine = implode(',', $http_response_header);
            preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
            $status = $match[1];
            $exceptionCode = self::HTTP_INTERNAL_SERVER_ERROR;

            // Check if response has valid HTTP status code
            if ($status && http_response_code($status)) {
                $exceptionCode = $status;
            }

            // Throw exception if request has failed
            if ($this->hasError($status)) {
                throw new Exception(
                    "Invalid response status: {$status}, Request URL: {$url}, " . $statusLine,
                    $exceptionCode
                );
            }
        }

        return new Response($response, $http_response_header);
    }

    /**
     * Has error in the request
     *
     * @param integer|null $status
     * @return boolean
     */
    private function hasError($status)
    {
        // Check if response status is other than 2xx & 3xx
        return strpos($status, '2') !== 0 && strpos($status, '3') !== 0;
    }

    /**
     * Get Instance
     * Can be used as a singleton.
     *
     * @param boolean $createNew
     * @return self
     */
    public static function getInstance(bool $createNew = false)
    {
        if ($createNew) {
            return new static();
        }

        // Create the instance if it does not exist.
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        // Return the unique instance.
        return self::$instance;
    }
}
