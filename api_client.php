<?php

require __DIR__ . '/vendor/autoload.php';

class ApiClient
{
    const LOG_INFO = LoggerLevel::INFO;
    const LOG_DEBUG = LoggerLevel::DEBUG;

    public $apiBaseUrl;
    public $apiToken;
    public $version;
    public $logLevel;
    public $logPath;

    private $apiBase;
    private $headers;
    private $logger;

    function __construct($apiBaseUrl, $apiToken, $version="2", $logLevel=LOG_INFO, $logPath=null) {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiToken = $apiToken;
        $this->version = $version;
        $this->logLevel = $logLevel;
        $this->logPath = $logPath ? $logPath : $_SERVER['DOCUMENT_ROOT'];
        
        $this->setup();
    }

    private function setup() {
        $this->setupApi();
        $this->setupLogging();
    }

    private function setupApi() {
        $this->apiBase = sprintf("%s/v%s", trim($this->apiBaseUrl, "/"), $this->version);
        $this->headers = array(
            'Authorization' => sprintf('ApiKey %s', $this->apiToken),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        );
    }

    private function setupLogging() {
        $this->logger = Logger::getLogger('Panopta API');
        $this->logger->setLevel($this->logLevel == LOG_INFO ? LoggerLevel::getLevelInfo() : LoggerLevel::getLevelDebug());
        
        $logAppender = new LoggerAppenderDailyFile();
        $logAppender->setFile(rtrim(rtrim($this->logPath, "/"), "\\") . DIRECTORY_SEPARATOR . "panopta_api.log");
        $logAppender->setAppend(true);
        $logAppender->setDatePattern("yyyy-MM-dd'.log'");
        $logAppender->setThreshold($this->logLevel);
        $pattern = new LoggerLayoutPattern();
        $pattern->setConversionPattern("%date - %logger - %level - %message%newline");
        $logAppender->setLayout($pattern);
        $logAppender->activateOptions();

        $this->logger->addAppender($logAppender);
    }

    private function http_parse_headers($header) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtolower("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }

    private function getResponse($statusCode, $statusReason, $content, $headers) {
        if (!$headers)
            $headers = array("status" => $statusCode);

        return array(
            'status_code' => $statusCode,
            'status_reason' => $statusReason,
            'response_data' => $content ? $content : array(),
            'response_headers' => $headers
        );
    }

    private function request($resourceUri, $method, $data, $headers) {
        $resourcePath = trim(sprintf("%s/%s", $this->apiBase, trim($resourceUri, "/")), "?");
        $headers = array_merge($headers ? $headers : array(), $this->headers);

        $requestHeaders = array();
        foreach ($headers as $key => $value)
            array_push($requestHeaders, sprintf("%s: %s", $key, $value));

        // Send request
        if ($this->logger->isInfoEnabled());
            $this->logger->info(sprintf('%s %s', $method, $resourcePath));

        $curlHandle = curl_init();

        if ($data) 
        {
            array_push($requestHeaders, "Content-Length: " . strlen($data));            
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_URL, $resourcePath);
        curl_setopt($curlHandle, CURLOPT_HEADER, true); 
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($curlHandle);
        $statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
        $resp = $this->http_parse_headers(substr($response, 0, $headerSize));
        $content = substr($response, $headerSize);
        curl_close($curlHandle);

        $resp['status'] = (string)$statusCode;
        try {
            $content = json_decode($content, true);
        } catch(Exception $err) {
            $content = array();
        }

        // Log request

        try {
            $data = json_decode($data, true);
        } catch(Exception $err) {
            $data = array();
        }

        $logData = array(
            'resource_path' => $resourcePath,
            'method' => $method,
            'request_headers' => $headers,
            'request_data' => $data,
            'response_headers' => $resp,
            'response_body' => $content
        );
        if ($this->logger->isDebugEnabled())
            $this->logger->debug(str_replace("\\/", "/", json_encode($logData)));

        // Prepare result

        $statusCode = $resp['status'];
        if (in_array($statusCode, array('200', '201', '204')))
            $statusReason = 'success';
        else {
            $reason = isset($resp['errormessage']) ? $resp['errormessage'] : null;
            if ($reason) 
                $statusReason = sprintf('error: %s', $reason);
            else
                $statusReason = 'error';
        }

        return $this->getResponse($statusCode, $statusReason, $content, $resp);
    }

    public function get($resourceUri, $queryParams=array(), $headers=array()) {
        return $this->request(sprintf("%s?%s", $resourceUri, http_build_query($queryParams)), "GET", null, $headers);
    }

    public function post($resourceUri, $requestData=array(), $headers=array()) {
        return $this->request($resourceUri, "POST", str_replace("\\/", "/", json_encode($requestData)), $headers);
    }

    public function put($resourceUri, $requestData=array(), $headers=array()) {
        return $this->request($resourceUri, "PUT", str_replace("\\/", "/", json_encode($requestData)), $headers);
    }

    public function delete($resourceUri, $headers=array()) {
        return $this->request($resourceUri, "DELETE", null, $headers);
    }
}

?>
