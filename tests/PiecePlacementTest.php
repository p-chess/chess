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

        $this->assertNull($chess->get('a1'));
        $this->assertNull($chess->get('a2'));
        $this->assertNull($chess->get('e4'));
        $this->assertNull($chess->get('g8'));

        $piece = new Piece(Piece::QUEEN, Piece::WHITE);
        $chess->put($piece, 'e4');
        $this->assertSame($chess->get('e4'), $piece);

        $piece = new Piece(Piece::KING, Piece::BLACK);
        $chess->put($piece, 'd3');
        $this->assertSame($chess->get('d3'), $piece);

        $chess->remove('d3');
        $this->assertNull($chess->get('d3'));

        $chess->remove('d3');
        $this->assertNull($chess->get('d3'));
    }

    public function testInvalidFen(): void
    {
        $chess = new Chess('8/8/8/8/8/8/8/8 w KQkq - 0 1');
        $result = $chess->load('invalid FEN string');
        $this->assertFalse($result);
    }
}
