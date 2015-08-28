Panopta API PHP Package
=======================
The Panopta REST API provides full access to all configuration, status and outage management
functionality of the Panopta monitoring service, including the ability to create and modify
monitoring checks that are being performed, manage notification configuration, respond
to active outages and to pull availability statistics for monitored servers.


# Installation
Run the [Composer](https://getcomposer.org/) `require` command:
```bash
composer require panopta/php-panopta-api-client
```

Add the Composer autoloader to your project:
```php
require 'vendor/autoload.php';
```

# API Documentation
Full documentation for the API is available at https://api2.panopta.com/v2/api-docs/.  By 
entering your API token you can view full details on all of the API methods and issue API
requests from the documentation page.  A token can be generated from the API management 
section of the Settings menu in the control panel at https://my.panopta.com.

# Usage
The library provides a wrapper around the Panopta REST API, making it easy to issue 
GET, POST, PUT and DELETE operations to the API.

## Instantiate the Panopta API client
```php
$client = Panopta\ApiClient(
    'https://api2.panopta.com',
    'your-api-token',
    2, // API version
    Panopta\ApiClient::LOG_DEBUG,
    'logs/' // Log directory
);
```
## GET
```php
$fiveContacts = $client->get('/contact', ['limit' => 5]);

$serversWithACertainFullyQualifiedDomainName = $client->get(
    '/server',
    ['fqdn' => 'panopta.com']
);

$serverFortyTwo = $client->get('/server/42');
```

## POST
```php
$newNotificationSchedule = $client->post(
    '/notification_schedule',
    ['name' => 'New Notification Schedule', 'targets' => [$serverFortyTwo['url']]]
);
```

## PUT
```php
$updatedServerGroup = $client->put(
    '/server_group',
    [
        'name' => 'Updated Server Group',
        'notification_schedule' => $newNotificationSchedule['url']
    ]
);
```

## DELETE
```php
$client->delete('/contact/1');
```
