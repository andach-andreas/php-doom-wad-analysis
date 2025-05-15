<?php

namespace Andach\DoomWadAnalysis\Lumps\Map;

use Andach\DoomWadAnalysis\LumpReader;

class Sectors
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        return $reader->readStructs(26, function (LumpReader $r) {
            return [
                'floor_height'     => $r->readInt16(),
                'ceiling_height'   => $r->readInt16(),
                'floor_texture'    => $r->readFixedLengthString(8),
                'ceiling_texture'  => $r->readFixedLengthString(8),
                'light_level'      => $r->readInt16(),
                'special'          => $r->readInt16(),
                'tag'              => $r->readInt16(),
            ];
        });
    }
}
