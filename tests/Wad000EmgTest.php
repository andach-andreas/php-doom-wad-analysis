<?php

use PHPUnit\Framework\TestCase;
use Andach\DoomWadAnalysis\WadAnalyser;

class Wad000EmgTest extends TestCase
{
    public function testMapCountInWad()
    {
        $analyser = new WadAnalyser([
            'colormap' => true,
            'playpal'  => true,
            'maps' => [
                'things'   => true,
                'linedefs' => true,
                'sidedefs' => true,
                'vertexes' => true,
                'textures' => true,
            ],
        ]);
        $result = $analyser->analyse(__DIR__ . '/wads/000emg.wad');

        $this->assertArrayHasKey('maps', $result);
        $this->assertIsArray($result['maps']);
        $this->assertCount(3, $result['maps'], 'Expected 3 maps in 000emg.wad');

        foreach (['MAP01', 'MAP02', 'MAP03'] as $mapName) {
            $this->assertArrayHasKey($mapName, $result['maps'], "Missing map: $mapName");
        }
    }
}
