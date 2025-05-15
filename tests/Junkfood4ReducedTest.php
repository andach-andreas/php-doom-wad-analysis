<?php

use PHPUnit\Framework\TestCase;
use Andach\DoomWadAnalysis\WadAnalyser;

class Junkfood4ReducedTest extends TestCase
{
    public function testMapCountInWad()
    {
        $analyser = new WadAnalyser();
        $result = $analyser->analyse(__DIR__ . '/wads/Junkfood4Reduced.wad');

        $this->assertArrayHasKey('maps', $result);
        $this->assertIsArray($result['maps']);
        $this->assertCount(1, $result['maps'], 'Expected 1 in Junkfood4Reduced.wad');
    }

    public function testColormapInWad()
    {
        $analyser = new WadAnalyser();
        $result = $analyser->analyse(__DIR__ . '/wads/Junkfood4Reduced.wad');

        $this->assertTrue($result['global']['has_colormap']);
        $this->assertCount(34, $result['global']['colormap']);

        foreach ($result['global']['colormap'] as $level) {
            $this->assertCount(256, $level);
        }
    }

    public function testPlaypalInWad()
    {
        $analyser = new WadAnalyser();
        $result = $analyser->analyse(__DIR__ . '/wads/Junkfood4Reduced.wad');

        $this->assertTrue($result['global']['has_playpal']);
        $this->assertCount(14, $result['global']['playpal']);

        foreach ($result['global']['playpal'] as $palette) {
            $this->assertCount(256, $palette);
        }

        $this->assertEquals(['r' => 0, 'g' => 0, 'b' => 0], $result['global']['playpal'][0][0]);
        $this->assertEquals(['r' => 26, 'g' => 19, 'b' => 9], $result['global']['playpal'][0][1]);
    }
}
