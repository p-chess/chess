<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PHPUnit\Framework\TestCase;

// source: https://chessprogramming.wikispaces.com/Perft+Results
class PerftTest extends TestCase
{
    // Position 1 Initial Position
    public function test1(): void
    {
        $chess = new ChessPublicator('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $this->assertSame($chess->perft(1), 20);
        $this->assertSame($chess->perft(2), 400);
        //~ $this->assertSame($chess->perft(3), 8902);
    }

    // Position 2 also know as "Kiwipete" by Peter McKenzie
    public function test2(): void
    {
        $chess = new ChessPublicator('r3k2r/p1ppqpb1/bn2pnp1/3PN3/1p2P3/2N2Q1p/PPPBBPPP/R3K2R w KQkq - 0 1');
        $this->assertSame($chess->perft(1), 48);
        $this->assertSame($chess->perft(2), 2039);
        //~ $this->assertSame($chess->perft(3), 97862);
    }

    // Position 3
    public function test3(): void
    {
        $chess = new ChessPublicator('8/2p5/3p4/KP5r/1R3p1k/8/4P1P1/8 w - - 0 1');
        $this->assertSame($chess->perft(1), 14);
        $this->assertSame($chess->perft(2), 191);
        //~ $this->assertSame($chess->perft(3), 2812);
    }

    // Position 4
    public function test4(): void
    {
        $chess = new ChessPublicator('r3k2r/Pppp1ppp/1b3nbN/nP6/BBP1P3/q4N2/Pp1P2PP/R2Q1RK1 w kq - 0 1');
        $this->assertSame($chess->perft(1), 6);
        $this->assertSame($chess->perft(2), 264);
        //~ $this->assertSame($chess->perft(3), 9467);
    }

    // Position 5
    public function test5(): void
    {
        $chess = new ChessPublicator('rnbq1k1r/pp1Pbppp/2p5/8/2B5/8/PPP1NnPP/RNBQK2R w KQ - 1 8');
        $this->assertSame($chess->perft(1), 44);
        $this->assertSame($chess->perft(2), 1486);
        //~ $this->assertSame($chess->perft(3), 62379);
    }

    // Position 6 An alternative Perft given by Steven Edwards
    public function test6(): void
    {
        $chess = new ChessPublicator('r4rk1/1pp1qppp/p1np1n2/2b1p1B1/2B1P1b1/P1NP1N2/1PP1QPPP/R4RK1 w - - 0 10');
        $this->assertSame($chess->perft(1), 46);
        $this->assertSame($chess->perft(2), 2079);
        //~ $this->assertSame($chess->perft(3), 89890);
    }

    // Tests that square '0' cannot be confused for the EP square when EP square is null
    public function test7(): void
    {
        $chess = new ChessPublicator('8/RPP5/8/3k4/5Bp1/6Pp/P4P1P/5K2 w - - 1 42');
        $this->assertSame($chess->perft(1), 26);
    }
}
