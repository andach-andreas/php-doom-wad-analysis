<?php

namespace Andach\DoomWadAnalysis\Lumps;

use Andach\DoomWadAnalysis\LumpReader;

class Playpal
{
    public static function parse(string $data): array
    {
        $reader = new LumpReader($data);
        $palettes = [];

        while (!$reader->isEOF()) {
            $palette = [];

            for ($i = 0; $i < 256; $i++) {
                $r = ord($reader->readBytes(1));
                $g = ord($reader->readBytes(1));
                $b = ord($reader->readBytes(1));
                $palette[] = ['r' => $r, 'g' => $g, 'b' => $b];
            }

            $palettes[] = $palette;
        }

        return $palettes;
    }
}
