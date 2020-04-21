<?php

declare(strict_types=1);

namespace PChess\Chess;

class History
{
    /** @var Move */
    public $move;

    /** @var string */
    public $position;

    /** @var array<string, int> */
    public $kings;

    /** @var string */
    public $turn;

    /** @var array<string, ?int> */
    public $castling;

    /** @var int|null */
    public $epSquare;

    /** @var int */
    public $halfMoves;

    /** @var int */
    public $moveNumber;

    /**
     * @param array<string, int>  $kings
     * @param array<string, ?int> $castling
     */
    public function __construct(
        Move $move,
        array $kings,
        string $turn,
        array $castling,
        ?int $epSquare,
        int $halfMoves,
        int $moveNumber
    ) {
        $this->move = $move;
        $this->kings = $kings;
        $this->turn = $turn;
        $this->castling = $castling;
        $this->epSquare = $epSquare;
        $this->halfMoves = $halfMoves;
        $this->moveNumber = $moveNumber;
    }
}
