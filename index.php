<?php
require 'vendor/autoload.php';

use App\Services\GoogleJsonStorage;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
    case '/':
        $store = new GoogleJsonStorage('rates.json', true);
        $url = $store->publicUrl();
        echo sprintf('<p>visit: <a href="%s">%s</a></p>', $url, $url);
        break;
    case '/update':
        require 'update.php';
        break;
    default:
        http_response_code(404);
        exit('Not Found');
}
