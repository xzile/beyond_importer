<?php

namespace App\Services;

use GuzzleHttp\Client;

class CharacterImporter
{

    /**
     * @param $characterId
     * @return Character
     */
    public function import($characterId)
    {
        /** @var Client $client */
        $client = app()->make(Client::class);
        $response = $client->get("https://www.dndbeyond.com/character/{$characterId}/json");

        /** @var Character $character */
        $character = app()->make(Character::class);
        $character->setData(json_decode($response->getBody()));
        return $character;
    }
}
