=======================
Panopta API PHP Package
=======================

The Panopta REST API provides full access to all configuration, status and outage management
functionality of the Panopta monitoring service, including the ability to create and modify
monitoring checks that are being performed, manage notification configuration, respond
to active outages and to pull availability statistics for monitored servers.


Installation
============

The included 


The library depends on the log4php/Apache_log4php package.  This can be installed with::

    pear channel-discover pear.apache.org/log4php
    pear install log4php/Apache_log4php


API Documentation
=================
Full documentation for the API is available at https://api2.panopta.com/v2/api-docs/.  By 
entering your API token you can view full details on all of the API methods and issue API
requests from the documentation page.  A token can be generated from the API management 
section of the Settings menu in the control panel at https://my.panopta.com.


Usage 
=====

The library provides a wrapper around the Panopta REST API, making it easy to issue 
GET, POST, PUT and DELETE operations to the API.  A sample use of the library is below::

     <?php

     require_once "api_client.php";

     $apiUrl = 'http://api2.panopta.com';
     $apiToken = 'testing';
     $version = '2';

     // initialize the client
     $client = new ApiClient($apiUrl, $apiToken, $version, ApiClient::LOG_DEBUG, '/tmp/'); 

     // get a server
     $queryParams = array( 'fqdn' => 'panopta.com', 'limit' => 10, 'offset' => 0 );
     $results = $client->get('/server', $queryParams);
     print_r($results);

     // create a contact
     $data = array( 'name' => 'john', 'timezone' => sprintf('%s/v%s/timezone/America/Chicago', 
                    $apiUrl, $version) );
     $results = $client->post('/contact', $data);
     print_r($results);

     ?>


