<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class Config
{
    /** @var \stdClass */
    public $data;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        // Check for cached config
        if (Cache::has('configJson')) {
            // Load from cache
            $configJson = Cache::pull('configJson');
        } else {
            // Load from the web
            /** @var Client $client */
            $client = app()->make(Client::class);
            $response = $client->get("https://www.dndbeyond.com/api/config/json");
            $configJson = (string)$response->getBody();

            // Cache the config
            Cache::put('configJson', $configJson, 60 * 24); // 1 day
        }

        $this->data = json_decode($configJson);
    }

    /**
     * @param $attr
     * @param $id
     * @param string $key
     * @return \Illuminate\Support\Collection
     */
    public function getById($attr, $id, $key = 'id')
    {
        return collect($this->data->{$attr})->firstWhere($key, $id);
    }
}
