<?php

namespace Andach\DoomWadAnalysis;

class TextFile
{
    private string $path;
    private string $text;

    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("WAD file not found: $path");
        }

        $this->path = $path;
        $this->text = file_get_contents($path);
    }

    function parse(): array
    {
        $fields = [
            'archive_maintainer' => '',
            'update_to' => '',
            'advanced_engine_needed' => '',
            'primary_purpose' => '',
            'title' => '',
            'filename' => '',
            'release_date' => '',
            'author' => '',
            'email_address' => '',
            'other_files_by_author' => '',
            'misc_author_info' => '',
            'description' => '',
            'credits' => '',
            'new_levels' => '',
            'sounds' => '',
            'music' => '',
            'graphics' => '',
            'dehacked_patch' => '',
            'demos' => '',
            'other' => '',
            'other_files_required' => '',
            'game' => '',
            'map' => '',
            'single_player' => '',
            'coop' => '',
            'deathmatch' => '',
            'other_game_styles' => '',
            'difficulty_settings' => '',
            'base' => '',
            'build_time' => '',
            'editors_used' => '',
            'known_bugs' => '',
            'may_not_run_with' => '',
            'tested_with' => '',
            'where_to_get_web' => '',
            'where_to_get_ftp' => '',
        ];

        $patterns = [
            'archive_maintainer' => '/^Archive Maintainer\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'update_to' => '/^Update to\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'advanced_engine_needed' => '/^Advanced engine needed\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'primary_purpose' => '/^Primary purpose\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'title' => '/^Title\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'filename' => '/^Filename\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'release_date' => '/^Release date\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'author' => '/^Author\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'email_address' => '/^Email Address\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'other_files_by_author' => '/^Other Files By Author\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'misc_author_info' => '/^Misc\. Author Info\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'description' => '/^Description\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'credits' => '/^Additional Credits to\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'new_levels' => '/^New levels\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'sounds' => '/^Sounds\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'music' => '/^Music\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'graphics' => '/^Graphics\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'dehacked_patch' => '/^Dehacked\/BEX Patch\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'demos' => '/^Demos\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'other' => '/^Other\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'other_files_required' => '/^Other files required\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'game' => '/^Game\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'map' => '/^Map #\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'single_player' => '/^Single Player\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'coop' => '/^Cooperative 2-4 Player\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'deathmatch' => '/^Deathmatch 2-4 Player\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'other_game_styles' => '/^Other game styles\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'difficulty_settings' => '/^Difficulty Settings\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'base' => '/^Base\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'build_time' => '/^Build Time\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'editors_used' => '/^Editor\(s\) used\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'known_bugs' => '/^Known Bugs\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'may_not_run_with' => '/^May Not Run With\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'tested_with' => '/^Tested With\s*:\s*((?:.*\n)+?)(?=^\S|\Z)/mi',
            'where_to_get_web' => '/^Web sites:\s*((?:.*\n?)+?)(?=^\S|\Z)/mi',
            'where_to_get_ftp' => '/^FTP sites:\s*((?:.*\n?)+?)(?=^\S|\Z)/mi',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $this->text, $matches)) {
                $fields[$key] = $this->combineMultipleLines($matches);
            }
        }

        return $fields;
    }

    private function combineMultipleLines($matches)
    {
        $lines = preg_split('/\r\n|\r|\n/', $matches[1]);
        $lines = array_map(fn($line) => ltrim($line), $lines);
        return trim(implode("\n", $lines));
    }
}
