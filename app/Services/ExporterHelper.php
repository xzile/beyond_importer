<?php

namespace App\Services;

use App\Services\Exporters\JsonExporter;

class ExporterHelper
{
    /**
     * @param string $type
     * @return mixed
     */
    public static function getExporter($type = 'json')
    {
        switch ($type) {
            case 'json':
            default:
                return app()->make(JsonExporter::class);
                break;
        }
    }
}
