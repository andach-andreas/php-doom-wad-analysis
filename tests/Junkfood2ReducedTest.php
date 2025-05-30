<?php

use PHPUnit\Framework\TestCase;
use Andach\DoomWadAnalysis\WadAnalyser;

class Junkfood2ReducedTest extends TestCase
{
    public function testMapValuesWad()
    {
        $analyser = new WadAnalyser([
            'maps' => [
                'counts' => true,
            ],
        ]);
        $result = $analyser->analyse(__DIR__ . '/wads/Junkfood2Reduced.wad');

        $this->assertArrayHasKey('maps', $result);
        $this->assertIsArray($result['maps']);

        $this->assertEquals(101, $result['maps']['MAP01']['counts']['things']);
        $this->assertEquals(1683, $result['maps']['MAP01']['counts']['linedefs']);

        $this->assertEquals(730, $result['maps']['MAP02']['counts']['things']);
        $this->assertEquals(2267, $result['maps']['MAP02']['counts']['linedefs']);
    }
}
