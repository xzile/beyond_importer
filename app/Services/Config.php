<?php

namespace App\Services;

use GuzzleHttp\Client;

class Config
{
    public $data;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        if ($this->data = cache()->pull('config') == null) {
            /** @var Client $client */
            $client = app()->make(Client::class);
            $response = $client->get("https://www.dndbeyond.com/api/config/json");
            $this->data = json_decode($response->getBody());

            // Cache the config
            cache()->put('config', json_decode($response->getBody()), 60 * 24); // 1 day
        }
    }

    public function getById($attr, $id, $key = 'id')
    {
        return collect($this->data->{$attr})->firstWhere($key, $id);
    }
}
