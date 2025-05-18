# PHP Doom Wad Analysis

This is a PHP class to analyse DOOM wads. It is currently in development. 

## Installation

```
composer install andach/php-doom-wad-analysis
```

## Usage

```
use Andach\DoomWadAnalysis\WadAnalyser;

$settings = [
    'colormap' => true,
    'playpal'  => true,
    'maps' => [
        'things'   => true,
        'linedefs' => true,
        'sidedefs' => true,
        'vertexes' => true,
        'textures' => true,
    ],
];

$analyser = new WadAnalyser($settings);
$result = $analyser->analyse('/path/to/file.wad');
```

## Testing

```
.\vendor\bin\phpunit.bat --coverage-html coverage
```

## Output

Calling `$analyser->analyse()` returns a deeply structured array that breaks down the contents of the WAD file. This output is intended for developers or tools that need to introspect, validate, visualize, or modify DOOM WAD data.

### Top-Level Structure

```php
[
    'type' => 'PWAD' | 'IWAD',
    'has_playpal' => true | false,
    'has_colormap' => true | false,
    'playpal' => [...],    // Present only if has_playpal is true
    'colormap' => [...],   // Present only if has_colormap is true
    'maps' => [
        'MAP01' => [
            'things' => [...],
            'linedefs' => [...],
            'sidedefs' => [...],
            'vertexes' => [...],
            'sectors' => [...],
        ],
        ...
    ]
]
```

### Maps Structure

#### Things

Entities such as players, monsters, items, decorations, etc.

```php
[
    [
        'x' => 128,
        'y' => 512,
        'angle' => 0,
        'type' => 1,
        'flags' => 7,
    ],
    ...
]
```

#### Linedefs
Defines the walls and logic lines between points.

```php
[
    [
        'start_vertex' => 0,
        'end_vertex' => 1,
        'flags' => 1,
        'special' => 0,
        'sector_tag' => 0,
        'right_sidedef' => 0,
        'left_sidedef' => -1,
    ],
    ...
]
```

#### Sidedefs

Texture information for each side of a wall (linedef).

```php
[
    [
        'x_offset' => 0,
        'y_offset' => 0,
        'upper_texture' => 'BRICK1',
        'lower_texture' => 'BRICK2',
        'middle_texture' => 'BRICK3',
        'sector' => 1,
    ],
    ...
]
```

#### Vertexes

2D points used by linedefs to form geometry.

```php
[
    [ 'x' => 128, 'y' => 64 ],
    ...
]
```

#### Sectors

Defines vertical space areas with floor/ceiling properties and lighting.

```php
[
    [
        'floor_height' => 0,
        'ceiling_height' => 128,
        'floor_texture' => 'FLAT1',
        'ceiling_texture' => 'CEIL1',
        'light_level' => 160,
        'special' => 0,
        'tag' => 0,
    ],
    ...
]
```