<?php

declare(strict_types=1);

namespace PChess\Chess;

class Entry
{
    public Move $move;

    public ?string $position;

    /** @var array<string, ?int> */
    public array $kings;

    public string $turn;

    /** @var array<string, ?int> */
    public array $castling;

    public ?int $epSquare;

    public int $halfMoves;

    public int $moveNumber;

    /**
     * @param array<string, ?int> $kings
     * @param array<string, ?int> $castling
     */
    public function __construct(
        Move $move,
        ?string $position,
        array $kings,
        array $castling,
        ?int $epSquare,
        int $halfMoves,
        int $moveNumber
    ) {
        $this->move = $move;
        $this->position = $position;
        $this->kings = $kings;
        $this->turn = $move->turn;
        $this->castling = $castling;
        $this->epSquare = $epSquare;
        $this->halfMoves = $halfMoves;
        $this->moveNumber = $moveNumber;
    }

    public function __toString(): string
    {
        return (string) $this->move;
    }
}
