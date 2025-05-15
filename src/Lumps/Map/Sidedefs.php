<?php

namespace Andach\DoomWadAnalysis\Lumps\Map;

use Andach\DoomWadAnalysis\LumpReader;

class Sidedefs
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        return $reader->readStructs(30, function (LumpReader $r) {
            return [
                'x_offset' => $r->readInt16(),
                'y_offset' => $r->readInt16(),
                'upper_texture' => $r->readFixedLengthString(8),
                'lower_texture' => $r->readFixedLengthString(8),
                'middle_texture' => $r->readFixedLengthString(8),
                'sector' => $r->readInt16(),
            ];
        });
    }
}
