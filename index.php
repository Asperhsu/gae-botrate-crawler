<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
    case '/':
        echo 'visit ' . env('MYJSON_ENDPOINT');
        break;
    case '/update':
        require 'update.php';
        break;
    default:
        http_response_code(404);
        exit('Not Found');
}
