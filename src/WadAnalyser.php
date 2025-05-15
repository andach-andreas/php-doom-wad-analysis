<?php

namespace Andach\DoomWadAnalysis;

use Andach\DoomWadAnalysis\Lumps\Colormap;
use Andach\DoomWadAnalysis\Lumps\Playpal;
use Andach\DoomWadAnalysis\Lumps\Map\Linedefs;
use Andach\DoomWadAnalysis\Lumps\Map\Sidedefs;
use Andach\DoomWadAnalysis\Lumps\Map\Vertexes;
use Andach\DoomWadAnalysis\Lumps\Map\Sectors;
use Andach\DoomWadAnalysis\Lumps\Map\Things;

class WadAnalyser
{
    protected WadFile $wadFile;

    public function analyse(string $path): array
    {
        $this->wadFile = new WadFile($path);

        $result = [
            'type' => $this->wadFile->getType(),
            'global' => $this->analyseGlobal(),
            'maps' => $this->analyseMaps(),
        ];

        return $result;
    }

    protected function analyseGlobal(): array
    {
        $globals = [];

        $colormapData = $this->wadFile->getLumpData('COLORMAP');
        $globals['has_colormap'] = $colormapData !== null;
        if ($colormapData !== null) {
            $globals['colormap'] = Colormap::parse($colormapData);
        }

        $playpalData = $this->wadFile->getLumpData('PLAYPAL');
        $globals['has_playpal'] = $playpalData !== null;
        if ($playpalData !== null) {
            $globals['playpal'] = Playpal::parse($playpalData);
        }

        // TODO: Detect custom enemies and weapons by scanning lumps or things

        return $globals;
    }

    protected function analyseMaps(): array
    {
        $lumps = $this->wadFile->getLumps();
        $maps = [];

        // Detect maps by lump name pattern
        // Doom map lumps start with ExMy (E1M1) or MAPxx (MAP01)
        $mapNames = [];
        foreach ($lumps as $lump) {
            if (preg_match('/^(E\dM\d|MAP\d\d)$/i', $lump['name'])) {
                $mapNames[] = strtoupper($lump['name']);
            }
        }
        $mapNames = array_unique($mapNames);

        foreach ($mapNames as $mapName) {
            $maps[$mapName] = $this->analyseSingleMap($mapName);
        }

        return $maps;
    }

    protected function analyseSingleMap(string $mapName): array
    {
        // Map lumps come immediately after the map name lump, in this order:
        // THINGS, LINEDEFS, SIDEDEFS, VERTEXES, SEGS, SSECTORS, NODES, SECTORS, REJECT, BLOCKMAP
        // We focus on THINGS, LINEDEFS, SIDEDEFS, VERTEXES, SECTORS

        $lumps = $this->wadFile->getLumps();

        // Find the index of the map name lump
        $index = null;
        foreach ($lumps as $i => $lump) {
            if (strcasecmp($lump['name'], $mapName) === 0) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            return [];
        }

        $mapData = [];

        // Helper to get lump data by relative index
        $getLumpData = function(int $offset) use ($lumps, $index) {
            $pos = $index + $offset;
            if (isset($lumps[$pos])) {
                return $this->wadFile->getLumpData($lumps[$pos]['name']);
            }
            return null;
        };

        // THINGS lump: index + 1
        $thingsData = $getLumpData(1);
        if ($thingsData !== null) {
            $mapData['things'] = Things::parse($thingsData);
        }

        // LINEDEFS lump: index + 2
        $linedefsData = $getLumpData(2);
        if ($linedefsData !== null) {
            $mapData['linedefs'] = Linedefs::parse($linedefsData);
        }

        // SIDEDEFS lump: index + 3
        $sidedefsData = $getLumpData(3);
        if ($sidedefsData !== null) {
            $mapData['sidedefs'] = Sidedefs::parse($sidedefsData);
        }

        // VERTEXES lump: index + 4
        $vertexesData = $getLumpData(4);
        if ($vertexesData !== null) {
            $mapData['vertexes'] = Vertexes::parse($vertexesData);
        }

        // SECTORS lump: index + 7 (skip 5,6: SEGS and SSECTORS)
        $sectorsData = $getLumpData(7);
        if ($sectorsData !== null) {
            $mapData['sectors'] = Sectors::parse($sectorsData);
        }

        return $mapData;
    }
}
