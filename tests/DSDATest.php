<?php

use Andach\DoomWadAnalysis\DSDA;
use PHPUnit\Framework\TestCase;
use Andach\DoomWadAnalysis\WadAnalyser;

class DSDATest extends TestCase
{
    public function testJunkfood()
    {
        $dsda = new DSDA('junkfood');

        $this->assertEquals('Also Reality', $dsda->records[96345]['comment']);

        $this->assertEquals('Record', $dsda->records[71552]['note']);
        $this->assertEquals('https://dsdarchive.com/files/demos/junkfood/71552/jf69str523.zip', $dsda->records[71552]['lmpURL']);
        $this->assertEquals('vNCCFaXMVNs', $dsda->records[71552]['youtubeID']);

        $this->assertEquals('Episode 1', $dsda->records[94701]['level']);
    }
}
