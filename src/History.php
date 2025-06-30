<?php

declare(strict_types=1);

namespace PChess\Chess;

final class History
{
    /** @param array<int, Entry> $entries */
    public function __construct(private array $entries = [])
    {
    }

    public function add(Entry $entry): void
    {
        $this->entries[] = $entry;
    }

    public function get(int $key): Entry
    {
        return $this->entries[$key];
    }

    public function pop(): ?Entry
    {
        if (\count($this->entries) < 1) {
            return null;
        }

        return \array_pop($this->entries);
    }

    /** @return array<int, Entry> */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function key(): ?int
    {
        return \key($this->entries);
    }
}
