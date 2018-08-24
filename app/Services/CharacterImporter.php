<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class CharacterImporter
{

    /**
     * @param $characterId
     * @return Character
     */
    public function import($characterId)
    {
        $cacheId = 'character-' . $characterId;
        // Check for cached config
        if (Cache::has($cacheId)) {
            // Load from cache
            $characterJson = Cache::pull($cacheId);
        } else {
            // Load from the web
            /** @var Client $client */
            $client = app()->make(Client::class);
            $response = $client->get("https://www.dndbeyond.com/character/{$characterId}/json");
            $characterJson = (string)$response->getBody();

            // Cache the config
            Cache::put($cacheId, $characterJson, 5); // 5 minutes
        }

        /** @var Character $character */
        $character = app()->make(Character::class);
        $character->setData(json_decode($characterJson));
        return $character;
    }
}
