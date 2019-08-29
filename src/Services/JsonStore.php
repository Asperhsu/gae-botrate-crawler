<?php

namespace App\Services;

use GuzzleHttp\Client;

class JsonStore
{
    protected $url;

    public function __construct()
    {
        $this->url = env('MYJSON_ENDPOINT');
    }

    public function save(array $data)
    {
        if (!$this->url) {
            throw new \Exception('Url Not specify');
        }

        $client = new Client;
        $response = $client->request('PUT', $this->url, [
            'json' => $data
        ]);

        return $response->getBody()->getContents();
    }
}
