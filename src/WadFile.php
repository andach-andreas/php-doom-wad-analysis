<?php

namespace Andach\DoomWadAnalysis;

class WadFile
{
    protected string $path;
    protected string $type; // IWAD or PWAD
    protected int $lumpCount;
    protected int $directoryOffset;
    protected array $lumps = [];

    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("WAD file not found: $path");
        }

        $this->path = $path;
        $this->parseHeaderAndDirectory();
    }

    protected function parseHeaderAndDirectory(): void
    {
        $handle = fopen($this->path, 'rb');
        if (!$handle) {
            throw new \RuntimeException("Failed to open WAD file.");
        }

        $this->type = fread($handle, 4); // IWAD or PWAD
        $this->lumpCount = unpack('V', fread($handle, 4))[1];
        $this->directoryOffset = unpack('V', fread($handle, 4))[1];

        fseek($handle, $this->directoryOffset);

        for ($i = 0; $i < $this->lumpCount; $i++) {
            $offset = unpack('V', fread($handle, 4))[1];
            $size   = unpack('V', fread($handle, 4))[1];
            $name   = rtrim(fread($handle, 8), "\0");

            $this->lumps[] = [
                'name'   => $name,
                'offset' => $offset,
                'size'   => $size,
            ];
        }

        fclose($handle);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLumps(): array
    {
        return $this->lumps;
    }

    public function getLumpData(string $name): ?string
    {
        foreach ($this->lumps as $lump) {
            if (strcasecmp($lump['name'], $name) === 0) {
                return $this->readBytes($lump['offset'], $lump['size']);
            }
        }
        return null;
    }

    protected function readBytes(int $offset, int $size): string
    {
        if (!$size)
        {
            return '';
        }

        $handle = fopen($this->path, 'rb');
        fseek($handle, $offset);
        $data = fread($handle, $size);
        fclose($handle);
        return $data;
    }
}
