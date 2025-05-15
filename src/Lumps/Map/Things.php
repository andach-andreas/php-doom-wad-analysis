<?php

namespace Andach\DoomWadAnalysis\Lumps\Map;

use Andach\DoomWadAnalysis\LumpReader;

class Things
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        return $reader->readStructs(10, function (LumpReader $r) {
            return [
                'x'     => $r->readInt16(),
                'y'     => $r->readInt16(),
                'angle' => $r->readUInt16(),
                'type'  => $r->readUInt16(),
                'flags' => $r->readUInt16(),
            ];
        });
    }
}
