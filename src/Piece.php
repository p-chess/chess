<?php

declare(strict_types=1);

namespace PChess\Chess;

final class Piece implements \JsonSerializable
{
    public const SYMBOLS = 'pnbrqkPNBRQK';

    public const PAWN = 'p';
    public const KNIGHT = 'n';
    public const BISHOP = 'b';
    public const ROOK = 'r';
    public const QUEEN = 'q';
    public const KING = 'k';

    public const BLACK = 'b';
    public const WHITE = 'w';

    public const PAWN_OFFSETS = [
        self::BLACK => [16,  32,  17,  15],
        self::WHITE => [-16, -32, -17, -15],
    ];

    public const OFFSETS = [
        self::KNIGHT => [-18, -33, -31, -14,  18,  33,  31,  14],
        self::BISHOP => [-17, -15,  17,  15],
        self::ROOK => [-16,   1,  16,  -1],
        self::QUEEN => [-17, -16, -15,   1,  17,  16,  15,  -1],
        self::KING => [-17, -16, -15,   1,  17,  16,  15,  -1],
    ];

    public const SHIFTS = [
        self::PAWN => 0,
        self::KNIGHT => 1,
        self::BISHOP => 2,
        self::ROOK => 3,
        self::QUEEN => 4,
        self::KING => 5,
    ];

    private string $type;

    private string $color;

    /** @var array<string> */
    private static $types = [
        self::PAWN,
        self::KNIGHT,
        self::BISHOP,
        self::ROOK,
        self::QUEEN,
        self::KING,
    ];

    /** @var array<string> */
    private static $colors = [self::BLACK, self::WHITE];

    /** @var array<string, string[]> */
    private static $pieces = [
        self::BLACK => [
            self::PAWN => '♟',
            self::KNIGHT => '♞',
            self::BISHOP => '♝',
            self::ROOK => '♜',
            self::QUEEN => '♛',
            self::KING => '♚',
        ],
        self::WHITE => [
            self::PAWN => '♙',
            self::KNIGHT => '♘',
            self::BISHOP => '♗',
            self::ROOK => '♖',
            self::QUEEN => '♕',
            self::KING => '♔',
        ],
    ];

    public function __construct(string $type, string $color)
    {
        if (!\in_array($type, self::$types, true)) {
            throw new \InvalidArgumentException('Invalid piece type');
        }
        if (!\in_array($color, self::$colors, true)) {
            throw new \InvalidArgumentException('Invalid piece color');
        }
        $this->type = $type;
        $this->color = $color;
    }

    public function __toString(): string
    {
        return $this->toAscii();
    }

    public function toAscii(): string
    {
        return $this->color === self::WHITE ? \strtoupper($this->type) : $this->type;
    }

    public function toUnicode(): string
    {
        return self::$pieces[$this->color][$this->type];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function isBishop(): bool
    {
        return $this->type === self::BISHOP;
    }

    public function isKing(): bool
    {
        return $this->type === self::KING;
    }

    public function isKnight(): bool
    {
        return $this->type === self::KNIGHT;
    }

    public function isPawn(): bool
    {
        return $this->type === self::PAWN;
    }

    public function jsonSerialize(): string
    {
        return $this->toAscii();
    }
}
