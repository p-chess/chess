<?php

declare(strict_types=1);

namespace PChess\Chess;

final class Move implements \JsonSerializable
{
    /** @var string */
    public $turn;

    /** @var int */
    public $flags;

    /** @var Piece */
    public $piece;

    /** @var string */
    public $from;

    /** @var string */
    public $to;

    /** @var int */
    public $fromSquare;

    /** @var int */
    public $toSquare;

    /** @var string|null */
    public $captured;

    /** @var string|null */
    public $promotion;

    /** @var string|null */
    public $san;

    public function __construct(string $turn, int $flags, Piece $piece, int $from, int $to, ?string $captured = null, ?string $promotion = null)
    {
        $this->turn = $turn;
        $this->flags = $flags;
        $this->piece = $piece;
        $this->fromSquare = $from;
        $this->toSquare = $to;
        $this->from = self::getSquare($from);
        $this->to = self::getSquare($to);
        $this->captured = $captured;
        $this->promotion = $promotion;
    }

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
            $captured = $board[$to]->getType();
        } elseif ($flags & Board::BITS['EP_CAPTURE']) {
            $captured = Piece::PAWN;
        }
        if ($promotion !== null) {
            $flags |= Board::BITS['PROMOTION'];
        }

        return new self(
            $turn,
            $flags,
            $board[$from],
            $from,
            $to,
            $captured,
            $promotion
        );
    }

    public function jsonSerialize(): string
    {
        return $this->turn.$this->flags.$this->piece->toAscii().$this->from.$this->to.$this->promotion;
    }

    private static function getSquare(int $code): string
    {
        $fen = \array_search($code, Board::SQUARES, true);
        if (false === $fen) {
            throw new \InvalidArgumentException('Invalid board code: '.$code);
        }

        return $fen;
    }
}
