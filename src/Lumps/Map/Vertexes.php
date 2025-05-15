<?php

namespace Andach\DoomWadAnalysis\Lumps\Map;

use Andach\DoomWadAnalysis\LumpReader;

class Vertexes
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        return $reader->readStructs(4, function (LumpReader $r) {
            return [
                'x' => $r->readInt16(),
                'y' => $r->readInt16(),
            ];
        });
    }
}
