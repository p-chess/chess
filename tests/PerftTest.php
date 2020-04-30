<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PHPUnit\Framework\TestCase;

// source: https://chessprogramming.wikispaces.com/Perft+Results
class PerftTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testPerft(string $fen, int $expectedDeep1, int $expectedDeep2): void
    {
        $chess = new ChessPublicator($fen);
        $this->assertEquals($expectedDeep1, $chess->perft(1));
        $chess->reset();
        $this->assertEquals($expectedDeep2, $chess->perft(2));
    }

    /**
     * @return array<string, array>
     */
    public function provider(): array
    {
        return [
            'initial position' => ['rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 20, 400],
            'Kiwipete' => ['r3k2r/p1ppqpb1/bn2pnp1/3PN3/1p2P3/2N2Q1p/PPPBBPPP/R3K2R w KQkq - 0 1', 48, 400],
            'position 3' => ['8/2p5/3p4/KP5r/1R3p1k/8/4P1P1/8 w - - 0 1', 14, 400],
            'position 4' => ['r3k2r/Pppp1ppp/1b3nbN/nP6/BBP1P3/q4N2/Pp1P2PP/R2Q1RK1 w kq - 0 1', 6, 400],
            'position 5' => ['rnbq1k1r/pp1Pbppp/2p5/8/2B5/8/PPP1NnPP/RNBQK2R w KQ - 1 8', 44, 400],
            // an alternative Perft given by Steven Edwards
            'Edwards' => ['r4rk1/1pp1qppp/p1np1n2/2b1p1B1/2B1P1b1/P1NP1N2/1PP1QPPP/R4RK1 w - - 0 10', 46, 400],
            // tests that square '0' cannot be confused for the EP square when EP square is null
            'ep square' => ['8/RPP5/8/3k4/5Bp1/6Pp/P4P1P/5K2 w - - 1 42', 26, 400],
        ];
    }
}
