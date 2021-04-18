<?php

declare(strict_types=1);

namespace PChess\Chess;

final class History
{
    /** @var array<int, Entry> */
    private $entries;

    /** @param array<int, Entry>|null $entries */
    public function __construct(array $entries = null)
    {
        $this->entries = $entries ?? [];
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
