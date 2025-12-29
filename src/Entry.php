<?php

declare(strict_types=1);

namespace PChess\Chess;

class Entry implements \Stringable
{
    public string $turn;

    /**
     * @param array<string, ?int> $kings
     * @param array<string, ?int> $castling
     */
    public function __construct(
        public Move $move,
        public ?string $position,
        public array $kings,
        public array $castling,
        public ?int $epSquare,
        public int $halfMoves,
        public int $moveNumber,
    ) {
        $this->turn = $move->turn;
    }

    public function __toString(): string
    {
        return (string) $this->move;
    }
}
