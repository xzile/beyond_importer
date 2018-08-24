<?php

namespace App\Http\Controllers;

use App\Services\CharacterImporter;
use App\Services\ExporterHelper;
use App\Services\Exporters\ExporterInterface;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    /**
     * @param Request $request
     * @param $characterId
     * @param string $output
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request, $characterId, $output = 'json')
    {
        /** @var CharacterImporter $importer */
        $importer = app()->make(CharacterImporter::class);
        $character = $importer->import($characterId);

        /** @var ExporterInterface $exporter */
        $exporter = ExporterHelper::getExporter($output);
        $exporter->loadCharacter($character);

        return $exporter->getResponse();
    }
}
