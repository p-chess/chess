<?php

declare(strict_types=1);

namespace PChess\Chess;

class Entry implements \Stringable
{
    /**
     * @param Position $before       state before the move (the board is shared, not copied)
     * @param ?string  $previousHash board hash before the move
     * @param ?string  $hash         board hash after the move
     */
    public function __construct(
        public Move $move,
        public Position $before,
        public ?string $previousHash = null,
        public ?string $hash = null,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->move;
    }
}
