<?php

class HttpRequest
{
    /**
     * Request URL
     * 
     * @var string
     */
    private $address;

    /**
     * Request `User-Agent` Header
     * 
     * @var string
     */
    public $userAgent = 'Mozilla/5.0 (compatible; PHP Request library)';

    /**
     * Connection Timeout in seconds
     * 
     * @var int
     */
    public $connectTimeout = 10;

    /**
     * Request Timeout in seconds
     * 
     * @var int
     */
    public $timeout = 15;

    /**
     * Whether cookies are enabled
     * 
     * @var bool
     */
    private $cookiesEnabled = false;

    /**
     * Path to the cookies file
     * 
     * @var string
     */
    private $cookiePath;

    /**
     * Whether SSL is enabled
     * 
     * @var bool
     */
    private $ssl = true;

    /**
     * Request Method
     * 
     * @var string
     */
    private $requestType;

    /**
     * HTTP request body
     * 
     * @var string
     */
    private $requestBody;

    /**
     * Request Headers
     * 
     * @var array
     */
    private $requestHeaders;

    /**
     * Latency in ms
     * 
     * @var int
     */
    private $latency;

    /**
     * HTTP response body
     * 
     * @var string|null
     */
    private $responseBody = null;

    /**
     * HTTP response header
     * 
     * @var string|null
     */
    private $responseHeader = null;

    /**
     * HTTP response status code
     * 
     * @var int
     */
    private $httpCode;

    /**
     * cURL Error
     * 
     * @var string
     */
    private $error;

    /**
     * Called when the Request object is created.
     * 
     * @param string $address The URI or IP address to request.
     */
    public function __construct($address)
    {
        if (!isset($address)) {
            throw new Exception("Error: Address not provided.");
        }
        $this->address = $address;
    }

    /**
     * Set the address for the request.
     *
     * @param string $address The URI or IP address to request.
     * @return HttpRequest
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Enable cookies.
     *
     * @param string $cookie_path Absolute path to a txt file where cookie information will be stored.
     * @return HttpRequest
     */
    public function enableCookies($cookie_path)
    {
        $this->cookiesEnabled = true;
        $this->cookiePath = $cookie_path;

        return $this;
    }

    /**
     * Disable cookies.
     * 
     * @return HttpRequest
     */
    public function disableCookies()
    {
        $this->cookiesEnabled = false;
        $this->cookiePath = '';

        return $this;
    }

    /**
     * Enable SSL.
     * 
     * @return HttpRequest
     */
    public function enableSSL()
    {
        $this->ssl = true;

        return $this;
    }

    /**
     * Disable SSL.
     * 
     * @return HttpRequest
     */
    public function disableSSL()
    {
        $this->ssl = false;

        return $this;
    }

    /**
     * Set HTTP Request headers
     * 
     * @param array $headers Request headers
     * @return HttpRequest
     */
    public function setRequestHeaders($headers)
    {
        $this->requestHeaders = $headers;

        return $this;
    }

    /**
     * Set timeout.
     *
     * @param int $timeout Timeout value in seconds.
     * @return HttpRequest
     */
    public function setTimeout($timeout = 15)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get timeout.
     *
     * @return int Timeout value in seconds.
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set connect timeout.
     *
     * @param int $connect_timeout Timeout value in seconds.
     * @return HttpRequest
     */
    public function setConnectTimeout($connectTimeout = 10)
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * Get connect timeout.
     *
     * @return int Timeout value in seconds.
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * Set a request method (by default, cURL will send a GET request).
     *
     * @param string $method Request Method (GET, POST, DELETE, PUT, etc.)
     * @return HttpRequest
     */
    public function setMethod($method)
    {
        $this->requestType = $method;

        return $this;
    }

    /**
     * Set the request body
     *
     * @param string $data HTTP Request Body
     * @return HttpRequest
     */
    public function setRequestBody($data)
    {
        $this->postFields = $data;

        return $this;
    }

    /**
     * Get the response body.
     *
     * @return string Response body.
     */
    public function getResponse()
    {
        return $this->responseBody;
    }

    /**
     * Get the response header.
     *
     * @return string Response header.
     */
    public function getHeader()
    {
        return $this->responseHeader;
    }

    /**
     * Get the HTTP status code for the response.
     *
     * @return int HTTP status code.
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Get the latency (the total time spent waiting) for the response.
     *
     * @return int Latency, in milliseconds.
     */
    public function getLatency()
    {
        return $this->latency;
    }

    /**
     * Get any cURL errors generated during the execution of the request.
     *
     * @return string An error message, if any error was given. Otherwise, empty.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Check a given address with cURL.
     *
     * After this method is completed, the response body, headers, latency, etc.
     * will be populated, and can be accessed with the appropriate methods.
     */
    public function execute()
    {
        $ch = curl_init();
        if (isset($this->requestHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        }
        if ($this->cookiesEnabled) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiePath);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiePath);
        }
        if (isset($this->requestType)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->requestType);
        }
        if (isset($this->requestBody)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->address);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        // Set the header, response, error and http code.
        $this->responseHeader = substr($response, 0, $header_size);
        $this->responseBody = substr($response, $header_size);
        $this->error = $error;
        $this->httpCode = $http_code;

        // Convert the latency to ms.
        $this->latency = round($time * 1000);
    }

    /**
     * @return string
     * @param string $method
     * @param string $data
     * @param string $url
     * @param array $headers
     */
    public static function hacky($url, $method, $data, $headers)
    {

        $opts = array(
            'http' =>
            array(
                'method'  => $method,
                'header'  => implode("\r\n", $headers),
                'content' => $data
            )
        );

        $context  = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    }
}
