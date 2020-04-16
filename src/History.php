<?php

declare(strict_types=1);

namespace PChess\Chess;

class History
{
    /** @var Move */
    public $move;

    /** @var string */
    public $position;

    /** @var array */
    public $kings;

    /** @var string */
    public $turn;

    /** @var array */
    public $castling;

    /** @var int|null */
    public $epSquare;

    /** @var int */
    public $halfMoves;

    /** @var int */
    public $moveNumber;

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
