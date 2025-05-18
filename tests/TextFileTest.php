<?php

use PHPUnit\Framework\TestCase;
use Andach\DoomWadAnalysis\TextFile;

class TextFileTest extends TestCase
{
    public function testParsesBlankTemplateCorrectly()
    {
        $textFile = new TextFile(__DIR__ . '/text/blank.txt');
        $result = $textFile->parse();

        $expected = [
            'archive_maintainer'     => 'Message To Archive Maintainer Here!',
            'update_to'              => 'example.wad',
            'advanced_engine_needed' => 'DSDA-Doom v24+',
            'primary_purpose'        => 'Single play',
            'title'                  => 'Test Wad Title',
            'filename'               => 'test-wad.wad',
            'release_date'           => '01/01/2000',
            'author'                 => 'John Smith',
            'email_address'          => 'john@smith.com',
            'other_files_by_author'  => "another-wad.wad\nthis-wad.wad",
            'misc_author_info'       => 'I am a man from Earth.',
            'description'            => 'This is a wad.',
            'credits'                => 'God',
            'new_levels'             => '10',
            'sounds'                 => 'Yes',
            'music'                  => 'Yes',
            'graphics'               => 'No',
            'dehacked_patch'         => 'No',
            'demos'                  => 'Yes',
            'other'                  => 'No',
            'other_files_required'   => 'None',
            'game'                   => 'Doom 2',
            'map'                    => 'Map01',
            'single_player'          => 'Designed for',
            'coop'                   => 'No',
            'deathmatch'             => 'No',
            'other_game_styles'      => 'None',
            'difficulty_settings'    => 'Yes',
            'base'                   => 'New',
            'build_time'             => '1 Days',
            'editors_used'           => 'SLADE',
            'known_bugs'             => 'None',
            'may_not_run_with'       => 'GZDoom',
            'tested_with'            => 'DSDA-Doom v29',
            'where_to_get_web'       => 'https://example.com',
            'where_to_get_ftp'       => 'https://ftp.example.com',
        ];

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            if ($key === 'license') {
                $this->assertStringContainsString('creativecommons.org/licenses/by/4.0', $text);
            } else {
                $this->assertSame($value, $result[$key], "Mismatch at key: $key");
            }
        }
    }
}

