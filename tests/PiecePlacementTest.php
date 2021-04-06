<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PChess\Chess\Piece;
use PHPUnit\Framework\TestCase;

class PiecePlacementTest extends TestCase
{
    public function testAll(): void
    {
        $chess = new Chess('8/8/8/8/8/8/8/8 w KQkq - 0 1');

        self::assertNull($chess->get('a1'));
        self::assertNull($chess->get('a2'));
        self::assertNull($chess->get('e4'));
        self::assertNull($chess->get('g8'));

        $piece = new Piece(Piece::QUEEN, Piece::WHITE);
        $chess->put($piece, 'e4');
        self::assertSame($chess->get('e4'), $piece);

        $piece = new Piece(Piece::KING, Piece::BLACK);
        $chess->put($piece, 'd3');
        self::assertSame($chess->get('d3'), $piece);

        $chess->remove('d3');
        self::assertNull($chess->get('d3'));

        $chess->remove('d3');
        self::assertNull($chess->get('d3'));
    }

    public function testInvalidFen(): void
    {
        $chess = new Chess('8/8/8/8/8/8/8/8 w KQkq - 0 1');
        $result = $chess->load('invalid FEN string');
        self::assertNotNull($result);
    }
}
