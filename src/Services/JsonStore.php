<?php

namespace App\Services;

use GuzzleHttp\Client;

class JsonStore
{
    protected $url;

    public function __construct()
    {
        $this->url = env('JSON_STORE_ENDPOINT');
    }

    public function save(array $data)
    {
        if (!$this->url) {
            throw new \Exception('Url Not specify');
        }

        $client = new Client;
        $response = $client->request('POST', $this->url, [
            'json' => $data
        ]);

        return $response->getBody()->getContents();
    }
}
