<?php

namespace App\Http\Controllers;

use App\Services\CharacterImporter;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public function export(Request $request, $characterId, $output = 'json')
    {
        /** @var CharacterImporter $importer */
        $importer = app()->make(CharacterImporter::class);
        $character = $importer->import($characterId);

        return response()->json($character->getData());
    }
}
