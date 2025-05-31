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
        print_r($demo->lmpstats());
        print_r($demo->tics);
    }
}
