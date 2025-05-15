<?php

namespace Andach\DoomWadAnalysis\Lumps\Map;

use Andach\DoomWadAnalysis\LumpReader;

class Linedefs
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        return $reader->readStructs(14, function (LumpReader $r) {
            return [
                'start_vertex' => $r->readUInt16(),
                'end_vertex'   => $r->readUInt16(),
                'flags'        => $r->readInt16(),
                'special'      => $r->readInt16(),
                'sector_tag'   => $r->readInt16(),
                'right_sidedef' => $r->readInt16(),
                'left_sidedef'  => $r->readInt16(),
            ];
        });
    }
}
