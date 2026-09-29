<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// source: https://www.chessprogramming.org/Perft_Results
final class PerftTest extends TestCase
{
    #[DataProvider('provider')]
    public static function testPerft(string $fen, int $expectedDepth1, int $expectedDepth2): void
    {
        $chess = new ChessPublicator($fen);
        self::assertSame($expectedDepth1, $chess->perft(1));
        self::assertSame($expectedDepth2, $chess->perft(2));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function provider(): array
    {
        return [
            'initial position' => ['rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', 20, 400],
            'Kiwipete' => ['r3k2r/p1ppqpb1/bn2pnp1/3PN3/1p2P3/2N2Q1p/PPPBBPPP/R3K2R w KQkq - 0 1', 48, 2039],
            'position 3' => ['8/2p5/3p4/KP5r/1R3p1k/8/4P1P1/8 w - - 0 1', 14, 191],
            'position 4' => ['r3k2r/Pppp1ppp/1b3nbN/nP6/BBP1P3/q4N2/Pp1P2PP/R2Q1RK1 w kq - 0 1', 6, 264],
            'position 5' => ['rnbq1k1r/pp1Pbppp/2p5/8/2B5/8/PPP1NnPP/RNBQK2R w KQ - 1 8', 44, 1486],
            // an alternative Perft given by Steven Edwards
            'Edwards' => ['r4rk1/1pp1qppp/p1np1n2/2b1p1B1/2B1P1b1/P1NP1N2/1PP1QPPP/R4RK1 w - - 0 10', 46, 2079],
            // tests that square '0' cannot be confused for the EP square when EP square is null
            'ep square' => ['8/RPP5/8/3k4/5Bp1/6Pp/P4P1P/5K2 w - - 1 42', 26, 149],
        ];
    }

    /**
     * @param array<string, int> $expected
     */
    #[DataProvider('fullProvider')]
    public static function testPerftFull(string $fen, int $depth, array $expected): void
    {
        $chess = new ChessPublicator($fen);
        self::assertSame($expected, $chess->perft($depth, true));
    }

    /**
     * @return array<string, array{string, int, array<string, int>}>
     */
    public static function fullProvider(): array
    {
        $initial = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
        $kiwipete = 'r3k2r/p1ppqpb1/bn2pnp1/3PN3/1p2P3/2N2Q1p/PPPBBPPP/R3K2R w KQkq - 0 1';
        $position3 = '8/2p5/3p4/KP5r/1R3p1k/8/4P1P1/8 w - - 0 1';
        $position4 = 'r3k2r/Pppp1ppp/1b3nbN/nP6/BBP1P3/q4N2/Pp1P2PP/R2Q1RK1 w kq - 0 1';

        return [
            'initial position, depth 2' => [$initial, 2, self::counts(400, 0, 0, 0, 0, 0, 0)],
            'Kiwipete, depth 1' => [$kiwipete, 1, self::counts(48, 8, 0, 2, 0, 0, 0)],
            'Kiwipete, depth 2' => [$kiwipete, 2, self::counts(2039, 351, 1, 91, 0, 3, 0)],
            // slow (~100k nodes), but the only reference case with a checkmate
            'Kiwipete, depth 3' => [$kiwipete, 3, self::counts(97862, 17102, 45, 3162, 0, 993, 1)],
            'position 3, depth 1' => [$position3, 1, self::counts(14, 1, 0, 0, 0, 2, 0)],
            'position 3, depth 2' => [$position3, 2, self::counts(191, 14, 0, 0, 0, 10, 0)],
            'position 4, depth 1' => [$position4, 1, self::counts(6, 0, 0, 0, 0, 0, 0)],
            'position 4, depth 2' => [$position4, 2, self::counts(264, 87, 0, 6, 48, 10, 0)],
        ];
    }

    /**
     * @return array<string, int>
     */
    private static function counts(int $nodes, int $captures, int $enPassants, int $castles, int $promotions, int $checks, int $checkmates): array
    {
        return \compact('nodes', 'captures', 'enPassants', 'castles', 'promotions', 'checks', 'checkmates');
    }
}
