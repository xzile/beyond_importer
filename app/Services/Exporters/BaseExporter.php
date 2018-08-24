<?php

namespace App\Services\Exporters;

use App\Services\Character;

class BaseExporter
{
    /** @var Character */
    protected $character;

    /**
     * @param Character $character
     * @return mixed
     */
    public function loadCharacter(Character $character)
    {
        $this->character = $character;
    }
}
