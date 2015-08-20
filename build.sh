mkdir panopta_rest_api
cp test_api_client.php api_client.php LICENSE  README panopta_rest_api
tar cvfz panopta_rest_api-php-`cat VERSION`.tgz panopta_rest_api
rm -rf panopta_rest_api