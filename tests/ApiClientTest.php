<?php
use Panopta\ApiClient;

class ApiClientTest extends PHPUnit_Framework_TestCase
{
    const API_URL = 'http://api2.panopta.com';
    const API_TOKEN = 'testing';
    const API_VERSION = 2;

    public function setUp() {
        $this->client = new ApiClient(
            self::API_URL,
            self::API_TOKEN,
            self::API_VERSION,
            ApiClient::LOG_DEBUG,
            '/tmp/'
        );
    }

    public function testGettingServer() {
        $queryParams = array( 'fqdn' => 'panopta.com', 'limit' => 10, 'offset' => 0 );
        $results = $this->client->get('/server', $queryParams);
        print_r($results);
    }

    public function testCreatingContact() {
        $data = array(
            'name' => 'john',
            'timezone' => sprintf('%s/v%s/timezone/America/Chicago', self::API_URL, self::API_VERSION)
        );
        $results = $this->client->post('/contact', $data);
        print_r($results);
    }
}
