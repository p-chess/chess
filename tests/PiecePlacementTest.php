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
        
        $this->assertSame($chess->get('a1'), null);
        $this->assertSame($chess->get('a2'), null);
        $this->assertSame($chess->get('e4'), null);
        $this->assertSame($chess->get('g8'), null);
        
        $piece = new Piece(Piece::QUEEN, Piece::WHITE);
        $chess->put($piece, 'e4');
        $this->assertSame($chess->get('e4'), $piece);
        
        $piece = new Piece(Piece::KING, Piece::BLACK);
        $chess->put($piece, 'd3');
        $this->assertSame($chess->get('d3'), $piece);
        
        $chess->remove('d3');
        $this->assertSame($chess->get('d3'), null);
        
        $chess->remove('d3');
        $this->assertSame($chess->get('d3'), null);
    }
}
