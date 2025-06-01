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
        $demo->convertTicsToCsv();
        print_r('a'.$demo->ticsCSV);
        die();
    }
}
