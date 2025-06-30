<?php

declare(strict_types=1);

namespace PChess\Chess;

final class Move implements \JsonSerializable, \Stringable
{
    public const BITS = [
        'NORMAL' => 1,
        'CAPTURE' => 2,
        'BIG_PAWN' => 4,
        'EP_CAPTURE' => 8,
        'PROMOTION' => 16,
        'KSIDE_CASTLE' => 32,
        'QSIDE_CASTLE' => 64,
    ];

    public string $from;

    public string $to;

    public int $fromSquare;

    public int $toSquare;

    public ?string $san = null;

    public function __construct(
        public string $turn,
        public int $flags,
        public Piece $piece,
        int $from,
        int $to,
        public ?string $captured = null,
        public ?string $promotion = null,
    ) {
        $this->fromSquare = $from;
        $this->toSquare = $to;
        $this->from = self::getSquare($from);
        $this->to = self::getSquare($to);
    }

    public function __toString(): string
    {
        return (string) $this->san;
    }

    public static function buildMove(
        string $turn,
        Board $board,
        int $from,
        int $to,
        int $flags,
        ?string $promotion = null,
    ): self {
        $captured = null;
        if ($board[$to] !== null) {
            $captured = $board[$to]->getType();
        } elseif ($flags & self::BITS['EP_CAPTURE']) {
            $captured = Piece::PAWN;
        }
        if ($promotion !== null) {
            $flags |= self::BITS['PROMOTION'];
        }
        if (!isset($board[$from])) {
            throw new \InvalidArgumentException('Invalid from: '.$from);
        }

        return new self(
            $turn,
            $flags,
            $board[$from],
            $from,
            $to,
            $captured,
            $promotion,
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
