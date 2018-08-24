<?php


namespace App\Services\Exporters;

use App\Services\Character;
use Illuminate\Http\Response;

interface ExporterInterface
{
    /**
     * @param Character $character
     * @return mixed
     */
    public function loadCharacter(Character $character);

    /**
     * @return Response
     */
    public function getResponse();
}
