<?php

use App\Services\BotRate;
use App\Services\JsonStore;

$botRate = new BotRate;
$data = $botRate->handle();

$store = new JsonStore;
$result = $store->save($data);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
