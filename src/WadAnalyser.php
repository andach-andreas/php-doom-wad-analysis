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
    public array $mapNames = [];
    protected array $settings = [];
    protected WadFile $wadFile;

    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    public function analyse(string $path): array
    {
        $this->wadFile = new WadFile($path);
        $this->mapNames = $this->parseMapNames();

        $result = $this->analyseGlobal();
        $result['type']   = $this->wadFile->getType();
        $result['maps']   = $this->analyseMaps();
        $result['counts'] = $this->sumCountsFromMaps($result['maps']);

        return $result;
    }

    protected function analyseGlobal(): array
    {
        $globals = [];

        if ($this->settings['colormap'] ?? false)
        {
            $colormapData = $this->wadFile->getLumpData('COLORMAP');
            $globals['has_colormap'] = $colormapData !== null;
            if ($colormapData !== null) {
                $globals['colormap'] = Colormap::parse($colormapData);
            }
        }

        if ($this->settings['playpal'] ?? false)
        {
            $playpalData = $this->wadFile->getLumpData('PLAYPAL');
            $globals['has_playpal'] = $playpalData !== null;
            if ($playpalData !== null) {
                $globals['playpal'] = Playpal::parse($playpalData);
            }
        }

        $globals['complevel'] = $this->detectComplevelFromLump();

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

        $mapData = ['name' => $this->getMapNameFromID($mapName)];

        // Helper to get lump data by relative index
        $getLumpData = function(int $offset) use ($lumps, $index) {
            $pos = $index + $offset;
            if (isset($lumps[$pos])) {
                return $this->wadFile->getLumpData($lumps[$pos]['name']);
            }
            return null;
        };

        $mapData['counts']['things']   = intdiv(strlen($getLumpData(1)), 10);
        $mapData['counts']['linedefs'] = intdiv(strlen($getLumpData(2)), 14);
        $mapData['counts']['sidedefs'] = intdiv(strlen($getLumpData(3)), 30);
        $mapData['counts']['vertexes'] = intdiv(strlen($getLumpData(4)), 4);
        $mapData['counts']['sectors']  = intdiv(strlen($getLumpData(8)), 26);

        // THINGS lump: index + 1
        if ($this->settings['maps']['things'] ?? false)
        {
            $thingsData = $getLumpData(1);
            if ($thingsData !== null) {
                $mapData['things'] = Things::parse($thingsData);
            }
        }

        // LINEDEFS lump: index + 2
        if ($this->settings['maps']['linedefs'] ?? false)
        {
            $linedefsData = $getLumpData(2);
            if ($linedefsData !== null) {
                $mapData['linedefs'] = Linedefs::parse($linedefsData);
            }
        }

        // SIDEDEFS lump: index + 3
        if ($this->settings['maps']['sidedefs'] ?? false)
        {
            $sidedefsData = $getLumpData(3);
            if ($sidedefsData !== null) {
                $mapData['sidedefs'] = Sidedefs::parse($sidedefsData);
            }
        }

        // VERTEXES lump: index + 4
        if ($this->settings['maps']['vertexes'] ?? false) {
            $vertexesData = $getLumpData(4);
            if ($vertexesData !== null) {
                $mapData['vertexes'] = Vertexes::parse($vertexesData);
            }
        }

        // SECTORS lump: index + 8 (skip 5,6,7: SEGS, SSECTORS and NODES)
        if ($this->settings['maps']['sectors'] ?? false) {
            $sectorsData = $getLumpData(8);
            if ($sectorsData !== null) {
                $mapData['sectors'] = Sectors::parse($sectorsData);
            }
        }

        return $mapData;
    }

    protected function detectComplevelFromLump(): ?int
    {
        $data = $this->wadFile->getLumpData('COMPLVL');

        if (!$data) {
            return null;
        }

        $value = strtolower(trim($data));

        return match ($value) {
            'vanilla' => 2, // or 3/4 depending on IWAD, but use 2 as baseline
            'boom' => 9,
            'mbf' => 11,
            'mbf21' => 21,
            default => null,
        };
    }

    public function getMapNameFromID(string $id): string
    {
        return $this->mapNames[$id] ?? '';
    }

    function parseMapNames()
    {
        $umapinfo = $this->parseMapNamesFromUmapinfo();    // highest priority
        $mapinfo  = $this->parseMapNamesFromMapinfo();     // medium priority
        $dehacked = $this->parseMapNamesFromDehacked();    // lowest priority

        $return = array_merge(
            $umapinfo,
            array_diff_key($mapinfo, $umapinfo),
            array_diff_key($dehacked, $umapinfo + $mapinfo)
        );

        if ($return)
        {
            return $return;
        }

        return [];
    }

    function parseMapNamesFromDehacked(): array
    {
        $mapNames = [];

        foreach (['DEHACKED', 'DEH'] as $lumpName) {
            $deh = $this->wadFile->getLumpData($lumpName);
            if ($deh)
            {
                if (preg_match_all('/^\s*LEVEL\s+NAME\s*:\s*(MAP\d\d|E\dM\d)/mi', $deh, $matches)) {
                    foreach ($matches[1] as $name) {
                        $mapNames[] = strtoupper($name);
                    }
                }
            }
        }

        return array_unique($mapNames);
    }

    function parseMapNamesFromMapinfo(): array
    {
        $mapinfo = $this->wadFile->getLumpData('MAPINFO');
        if (!$mapinfo) {
            return [];
        }
        $lines = preg_split('/\R/', $mapinfo);
        $mapNames = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*map\s+(\S+)\s*,/i', $line, $match)) {
                $mapNames[] = strtoupper($match[1]);
            }
        }

        return array_unique($mapNames);
    }

    function parseMapNamesFromUmapinfo(): array
    {
        $umapinfo = $this->wadFile->getLumpData('UMAPINFO');
        if (!$umapinfo) {
            return [];
        }

        $lines = preg_split('/\R/', $umapinfo);
        $mapNames = [];

        $currentMap = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^MAP\s+(\S+)/i', $line, $match)) {
                $currentMap = strtoupper($match[1]);
                continue;
            }

            if ($currentMap && preg_match('/^levelname\s*=\s*"([^"]+)"/i', $line, $match)) {
                $mapNames[$currentMap] = $match[1];
                // only reset after assignment
                $currentMap = null;
            }
        }

        return $mapNames;
    }


    protected function sumCountsFromMaps(array $maps): array
    {
        $totals = [
            'maps' => count($maps),
            'things' => 0,
            'linedefs' => 0,
            'sidedefs' => 0,
            'vertexes' => 0,
            'sectors' => 0,
        ];

        foreach ($maps as $map) {
            $totals['things']     += $map['counts']['things']     ?? 0;
            $totals['linedefs']   += $map['counts']['linedefs']   ?? 0;
            $totals['sidedefs']   += $map['counts']['sidedefs']   ?? 0;
            $totals['vertexes']    += $map['counts']['vertexes']    ?? 0;
            $totals['sectors']    += $map['counts']['sectors']    ?? 0;
        }

        return $totals;
    }
}
