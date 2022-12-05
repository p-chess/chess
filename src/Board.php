<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * @implements \ArrayAccess<int, ?Piece>
 * @implements \Iterator<int, ?Piece>
 */
class Board implements \ArrayAccess, \Iterator, \JsonSerializable
{
    public const DEFAULT_POSITION = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

    public const EMPTY = '8/8/8/8/8/8/8/8 w - - 0 1';

    public const SQUARES = [
        'a8' => 0, 'b8' => 1, 'c8' => 2, 'd8' => 3, 'e8' => 4, 'f8' => 5, 'g8' => 6, 'h8' => 7,
        'a7' => 16, 'b7' => 17, 'c7' => 18, 'd7' => 19, 'e7' => 20, 'f7' => 21, 'g7' => 22, 'h7' => 23,
        'a6' => 32, 'b6' => 33, 'c6' => 34, 'd6' => 35, 'e6' => 36, 'f6' => 37, 'g6' => 38, 'h6' => 39,
        'a5' => 48, 'b5' => 49, 'c5' => 50, 'd5' => 51, 'e5' => 52, 'f5' => 53, 'g5' => 54, 'h5' => 55,
        'a4' => 64, 'b4' => 65, 'c4' => 66, 'd4' => 67, 'e4' => 68, 'f4' => 69, 'g4' => 70, 'h4' => 71,
        'a3' => 80, 'b3' => 81, 'c3' => 82, 'd3' => 83, 'e3' => 84, 'f3' => 85, 'g3' => 86, 'h3' => 87,
        'a2' => 96, 'b2' => 97, 'c2' => 98, 'd2' => 99, 'e2' => 100, 'f2' => 101, 'g2' => 102, 'h2' => 103,
        'a1' => 112, 'b1' => 113, 'c1' => 114, 'd1' => 115, 'e1' => 116, 'f1' => 117, 'g1' => 118, 'h1' => 119,
    ];

    public const ROOKS = [
        Piece::WHITE => [['square' => self::SQUARES['a1'], 'flag' => Move::BITS['QSIDE_CASTLE']],
            ['square' => self::SQUARES['h1'], 'flag' => Move::BITS['KSIDE_CASTLE']], ],
        Piece::BLACK => [['square' => self::SQUARES['a8'], 'flag' => Move::BITS['QSIDE_CASTLE']],
            ['square' => self::SQUARES['h8'], 'flag' => Move::BITS['KSIDE_CASTLE']], ],
    ];

    public const RANK_1 = 7;
    public const RANK_2 = 6;
    public const RANK_7 = 1;
    public const RANK_8 = 0;

    public const ATTACKS = [
        20, 0, 0, 0, 0, 0, 0, 24,  0, 0, 0, 0, 0, 0,20, 0,
        0,20, 0, 0, 0, 0, 0, 24,  0, 0, 0, 0, 0,20, 0, 0,
        0, 0,20, 0, 0, 0, 0, 24,  0, 0, 0, 0,20, 0, 0, 0,
        0, 0, 0,20, 0, 0, 0, 24,  0, 0, 0,20, 0, 0, 0, 0,
        0, 0, 0, 0,20, 0, 0, 24,  0, 0,20, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0,20, 2, 24,  2,20, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 2,53, 56, 53, 2, 0, 0, 0, 0, 0, 0,
        24,24,24,24,24,24,56,  0, 56,24,24,24,24,24,24, 0,
        0, 0, 0, 0, 0, 2,53, 56, 53, 2, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0,20, 2, 24,  2,20, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0,20, 0, 0, 24,  0, 0,20, 0, 0, 0, 0, 0,
        0, 0, 0,20, 0, 0, 0, 24,  0, 0, 0,20, 0, 0, 0, 0,
        0, 0,20, 0, 0, 0, 0, 24,  0, 0, 0, 0,20, 0, 0, 0,
        0,20, 0, 0, 0, 0, 0, 24,  0, 0, 0, 0, 0,20, 0, 0,
        20, 0, 0, 0, 0, 0, 0, 24,  0, 0, 0, 0, 0, 0,20,
    ];

    public const RAYS = [
        17,  0,  0,  0,  0,  0,  0, 16,  0,  0,  0,  0,  0,  0, 15, 0,
        0, 17,  0,  0,  0,  0,  0, 16,  0,  0,  0,  0,  0, 15,  0, 0,
        0,  0, 17,  0,  0,  0,  0, 16,  0,  0,  0,  0, 15,  0,  0, 0,
        0,  0,  0, 17,  0,  0,  0, 16,  0,  0,  0, 15,  0,  0,  0, 0,
        0,  0,  0,  0, 17,  0,  0, 16,  0,  0, 15,  0,  0,  0,  0, 0,
        0,  0,  0,  0,  0, 17,  0, 16,  0, 15,  0,  0,  0,  0,  0, 0,
        0,  0,  0,  0,  0,  0, 17, 16, 15,  0,  0,  0,  0,  0,  0, 0,
        1,  1,  1,  1,  1,  1,  1,  0, -1, -1,  -1,-1, -1, -1, -1, 0,
        0,  0,  0,  0,  0,  0,-15,-16,-17,  0,  0,  0,  0,  0,  0, 0,
        0,  0,  0,  0,  0,-15,  0,-16,  0,-17,  0,  0,  0,  0,  0, 0,
        0,  0,  0,  0,-15,  0,  0,-16,  0,  0,-17,  0,  0,  0,  0, 0,
        0,  0,  0,-15,  0,  0,  0,-16,  0,  0,  0,-17,  0,  0,  0, 0,
        0,  0,-15,  0,  0,  0,  0,-16,  0,  0,  0,  0,-17,  0,  0, 0,
        0,-15,  0,  0,  0,  0,  0,-16,  0,  0,  0,  0,  0,-17,  0, 0,
        -15,  0,  0,  0,  0,  0,  0,-16,  0,  0,  0,  0,  0,  0,-17,
    ];

    /** @var array<int, ?Piece> */
    private array $squares = [];

    private bool $reversed = false;

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->squares[$offset]);
    }

    /**
     * @param int $offset
     */
    public function offsetGet($offset): ?Piece
    {
        return $this->squares[$offset] ?? null;
    }

    /**
     * @param int    $offset
     * @param ?Piece $value
     */
    public function offsetSet($offset, $value): void
    {
        if (\in_array($offset, self::SQUARES, true)) {
            $this->squares[$offset] = $value;
        }
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->squares[$offset]);
    }

    public function current(): ?Piece
    {
        return \current($this->squares) !== false ? \current($this->squares): null;
    }

    public function next(): void
    {
        \next($this->squares);
    }

    public function key(): ?int
    {
        return \key($this->squares);
    }

    public function valid(): bool
    {
        return null !== $this->key();
    }

    public function rewind(): void
    {
        \reset($this->squares);
    }

    public function jsonSerialize(): string
    {
        return \implode(' ', $this->squares);
    }

    public function reverse(): void
    {
        $this->squares = \array_reverse($this->squares, true);
        $this->rewind();
        $this->reversed = !$this->reversed;
    }

    public function isReversed(): bool
    {
        return $this->reversed;
    }

    public static function rank(int $i): int
    {
        return $i >> 4;
    }

    public static function file(int $i): int
    {
        return $i & 15;
    }

    public static function algebraic(int $i): string
    {
        $file = self::file($i);
        $rank = self::rank($i);

        return \substr('abcdefgh', $file, 1).\substr('87654321', $rank, 1);
    }
}
