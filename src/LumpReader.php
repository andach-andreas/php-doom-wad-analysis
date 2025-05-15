<?php

namespace Andach\DoomWadAnalysis;

class LumpReader
{
    protected string $data;
    protected int $offset = 0;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function readBytes(int $length): string
    {
        $slice = substr($this->data, $this->offset, $length);
        $this->offset += $length;
        return $slice;
    }

    public function readUInt8(): int
    {
        $bytes = $this->readBytes(1);
        return unpack('C', $bytes)[1];
    }

    public function readInt16(): int
    {
        $raw = unpack('v', substr($this->data, $this->offset, 2))[1];
        $this->offset += 2;

        return $raw > 0x7FFF ? $raw - 0x10000 : $raw;
    }

    public function readUInt16(): int
    {
        $value = unpack('v', substr($this->data, $this->offset, 2))[1];
        $this->offset += 2;
        return $value;
    }

    public function readFixedLengthString(int $length): string
    {
        return rtrim($this->readBytes($length), "\0");
    }

    public function readStructs(int $structSize, callable $readerFunc): array
    {
        $results = [];
        $count = intdiv(strlen($this->data), $structSize);

        for ($i = 0; $i < $count; $i++) {
            $results[] = $readerFunc($this);
        }

        return $results;
    }
}
