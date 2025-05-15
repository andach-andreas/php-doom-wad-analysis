<?php

namespace Andach\DoomWadAnalysis\Lumps;

use Andach\DoomWadAnalysis\LumpReader;

class Playpal
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);

        // Each color is 3 bytes (RGB), 256 colors per palette
        // So one palette is 768 bytes (256 * 3)
        // We read palettes sequentially until the data ends

        return $reader->readStructs(768, function (LumpReader $r) {
            $palette = [];

            for ($i = 0; $i < 256; $i++) {
                $palette[] = [
                    'r' => $r->readUInt8(),
                    'g' => $r->readUInt8(),
                    'b' => $r->readUInt8(),
                ];
            }

            return $palette;
        });
    }
}
