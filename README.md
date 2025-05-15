# PHP Doom Wad Analysis

This is a PHP class to analyse DOOM wads. It is currently in development. 

## Installation

```
composer install andach/php-doom-wad-analysis
```

## Usage

```
use Andach\DoomWadAnalysis\WadAnalyser;

$analyser = new WadAnalyser();
$result = $analyser->analyse(__DIR__ . '/wads/000emg.wad');
```

## Testing

```
.\vendor\bin\phpunit.bat --coverage-html coverage
```