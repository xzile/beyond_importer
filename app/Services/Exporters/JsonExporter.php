<?php

namespace App\Services\Exporters;

class JsonExporter extends BaseExporter implements ExporterInterface
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function getResponse()
    {
        $data = $this->getCharacterData();

        return response()->json($data);
    }

    /**
     * @return array
     */
    private function getCharacterData()
    {
        $methods = array_diff($this->character->getMethods(), get_class_methods(get_class($this->character)));

        $data = [];
        foreach ($methods as $method) {
            $data[$method] = $this->character->$method;
        }

        return $data;
    }
}
