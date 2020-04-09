<?php

declare(strict_types=1);

namespace PChess\Chess;

class Piece
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
        self::KING => 5
    ];

    /** @var string */
    public $type;

    /** @var string */
    public $color;

    /** @var array */
    private static $types = [
        self::PAWN,
        self::KNIGHT,
        self::BISHOP,
        self::ROOK,
        self::QUEEN,
        self::KING,
    ];

    /** @var array */
    private static $colors = [Piece::BLACK, self::WHITE];

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
        if (!in_array($type, self::$types, true)) {
            throw new \InvalidArgumentException('Invalid piece type');
        }
        if (!in_array($color, self::$colors, true)) {
            throw new \InvalidArgumentException('Invalid piece color');
        }
        $this->type = $type;
        $this->color = $color;
    }

    public function __toString(): string
    {
        return self::$pieces[$this->color][$this->type];
    }
}
