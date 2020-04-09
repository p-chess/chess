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
}
