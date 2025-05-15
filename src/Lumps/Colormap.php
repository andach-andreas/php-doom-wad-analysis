<?php

namespace Andach\DoomWadAnalysis\Lumps;

use Andach\DoomWadAnalysis\LumpReader;

class Colormap
{
    public static function parse(string $data): array
    {
        // 34 levels × 256 bytes = 8704 bytes
        $reader = new LumpReader($data);
        $levels = [];

        for ($i = 0; !$reader->isEOF() && $i < 34; $i++) {
            $levels[] = array_values(unpack('C*', $reader->readBytes(256)));
        }

        return $levels;
    }
}
