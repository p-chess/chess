<?php

declare(strict_types=1);

namespace PChess\Chess;

class Move
{
    /** @var string */
    public $turn;

    /** @var int */
    public $flags;

    /** @var string */
    public $piece;

    /** @var int */
    public $from;

    /** @var int */
    public $to;

    /** @var string|null */
    public $captured;

    /** @var string|null */
    public $promotion;

    /** @var string|null */
    public $san;

    public function __construct(string $turn, int $flags, string $piece, int $from, int $to, ?string $captured = null, ?string $promotion = null)
    {
        $this->turn = $turn;
        $this->flags = $flags;
        $this->piece = $piece;
        $this->from = $from;
        $this->to = $to;
        $this->captured = $captured;
        $this->promotion = $promotion;
    }

    // here, we add first parameter turn, to make this really static method
    // because in chess.js var turn got from outside scope,
    // maybe need a little fix in chess.js or maybe i am :-p
    public static function buildMove(
        string $turn,
        Board $board,
        int $from,
        int $to,
        int $flags,
        ?string $promotion = null
    ): self {
        $captured = null;
        if ($board[$to] !== null) {
            $captured = $board[$to]->type;
        } elseif ($flags & Board::BITS['EP_CAPTURE']) {
            $captured = Piece::PAWN;
        }
        if ($promotion !== null) {
            $flags |= Board::BITS['PROMOTION'];
        }

        return new self(
            $turn,
            $flags,
            $board[$from]->type,
            $from,
            $to,
            $captured,
            $promotion
        );
    }
}
