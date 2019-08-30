<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

use App\Services\BotRate;
use App\Services\GoogleJsonStorage;

$botRate = new BotRate;
$data = $botRate->handle();

$store = new GoogleJsonStorage('rates.json', true);
$store->store($data);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
