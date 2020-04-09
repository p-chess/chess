<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PHPUnit\Framework\TestCase;

class MiscTest extends TestCase
{
    public function testSquareColor(): void
    {
        $chess = new Chess;
        $this->assertSame($chess->squareColor('a1'), 'dark');
        $this->assertSame($chess->squareColor('b2'), 'dark');
        $this->assertSame($chess->squareColor('b3'), 'light');
        $this->assertSame($chess->squareColor('e4'), 'light');
        $this->assertSame($chess->squareColor('e5'), 'dark');
        $this->assertSame($chess->squareColor('aw'), null);
    }
    
    public function testHistorySAN(): void
    {
        $chess = new ChessPublicator();
        $moves = ['e4','e6','d4','d5','Nc3','Nf6','Bg5','dxe4','Nxe4','Be7','Bxf6','gxf6','g3','f5','Nc3','Bf6'];
        
        foreach ($moves as $move) {
            $this->assertNotNull($chess->move($move), $move);
        }
        $this->assertSame($chess->history(), $moves);
    }
    
    public function testHistoryPrettyMoves(): void
    {
        $chess = new ChessPublicator();
        $moves = ['e4','e6','d4','d5','Nc3','Nf6','Bg5','dxe4','Nxe4','Be7','Bxf6','gxf6','g3','f5','Nc3','Bf6'];
        
        foreach ($moves as $move) {
            $this->assertNotNull($chess->move($move), $move);
        }
        $histories = $chess->history(['verbose' => true]);
        
        $this->assertSame(count($histories), count($moves));
        foreach ($histories as $k => $history) {
            $this->assertSame($history->san, $moves[$k]);
        }
    }
}
