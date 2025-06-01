<?php

use Andach\DoomWadAnalysis\Demo;
use Andach\DoomWadAnalysis\DSDA;
use PHPUnit\Framework\TestCase;
use Andach\DoomWadAnalysis\WadAnalyser;

class DemoTest extends TestCase
{
    public function testDemo()
    {
        $demo = new Demo(__DIR__ . '/demos/junk67m1134.lmp');
        $demo->lmpStats();

        $this->assertSame(
            [
                'version' => 202,
                'skill_number' => 4,
                'mode_number' => 0,
                'respawn' => 0,
                'fast' => 0,
                'nomonsters' => 0,
                'number_of_players' => 1,
                'tics' => 24551,
                'secs' => 41.46,
            ],
            $demo->stats
        );
    }
}
