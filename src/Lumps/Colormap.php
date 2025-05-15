<?php

namespace Andach\DoomWadAnalysis\Lumps;

use Andach\DoomWadAnalysis\LumpReader;

class Colormap
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        // Each level is 256 bytes (one color map)
        // There are 34 levels total

        return $reader->readStructs(256, function (LumpReader $r) {
            return array_values(unpack('C*', $r->readBytes(256)));
        });
    }
}
