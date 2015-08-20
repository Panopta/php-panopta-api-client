<?php

require_once "api_client.php";

$apiUrl = 'http://api2.panopta.com';
$apiToken = 'testing';
$version = '2';

// initialize the client
$client = new ApiClient($apiUrl, $apiToken, $version, ApiClient::LOG_DEBUG, '/tmp/'); //$_SERVER['DOCUMENT_ROOT']);

// get a server
$queryParams = array( 'fqdn' => 'panopta.com', 'limit' => 10, 'offset' => 0 );
$results = $client->get('/server', $queryParams);
print_r($results);

// create a contact
$data = array( 'name' => 'john', 'timezone' => sprintf('%s/v%s/timezone/America/Chicago', $apiUrl, $version) );
$results = $client->post('/contact', $data);
print_r($results);

?>
