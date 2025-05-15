#!/usr/bin/env php
<?php

use Andach\DoomWadAnalysis\WadAnalyser;

require __DIR__ . '/vendor/autoload.php';

$path = $argv[1] ?? null;

if (!$path || !file_exists($path)) {
    echo "Usage: php example.php path/to/wadfile.wad\n";
    exit(1);
}

$analyser = new WadAnalyser();
$result = $analyser->analyse($path);

print_r($result);
