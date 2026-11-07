<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PChess\Chess\Move;
use PChess\Chess\SanEncoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SanEncoderTest extends TestCase
{
    #[DataProvider('disambiguatorProvider')]
    public function testDisambiguator(string $fen, string $from, string $to, string $expected): void
    {
        $moves = (new Chess($fen))->moves();
        self::assertSame($expected, SanEncoder::disambiguator(self::find($moves, $from, $to), $moves));
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function disambiguatorProvider(): array
    {
        return [
            'none' => ['5k2/8/3p2R1/8/2N5/8/1K6/8 w - - 0 1', 'c4', 'd6', ''],
            'file' => ['5k2/8/3p4/8/2N1N3/8/1K6/8 w - - 0 1', 'e4', 'd6', 'e'],
            'rank' => ['2N2k2/8/3p4/8/2N5/8/1K6/8 w - - 0 1', 'c4', 'd6', '4'],
            'square' => ['8/8/8/2qqq3/2qPq3/2qqq3/1n6/K6k b - - 0 1', 'd5', 'd4', 'd5'],
        ];
    }

    public function testEncodeUsesInjectedCallbacks(): void
    {
        // white rook to f8 gives check: Rf8+
        $chess = new Chess('3R4/2K5/8/5k2/8/8/8/8 w - - 0 1');
        $move = self::find($chess->moves(), 'd8', 'f8');

        $calls = [];
        $encoder = new SanEncoder(
            static function () use (&$calls, $move): array {
                $calls[] = 'generate';

                return [$move];
            },
            static function (Move $move) use (&$calls): void {
                $calls[] = 'make';
            },
            static function () use (&$calls): void {
                $calls[] = 'undo';
            },
            static function () use (&$calls): bool {
                $calls[] = 'inCheck';

                return true;
            },
        );

        self::assertSame('Rf8+', $encoder->encode($move, 'hash'));
        self::assertSame(['generate', 'make', 'inCheck', 'generate', 'undo'], $calls);

        // same move and hash: served from cache, no callback invoked
        $calls = [];
        self::assertSame('Rf8+', $encoder->encode($move, 'hash'));
        self::assertSame([], $calls);
    }

    /**
     * @param array<int, Move> $moves
     */
    private static function find(array $moves, string $from, string $to): Move
    {
        foreach ($moves as $move) {
            if ($move->from === $from && $move->to === $to) {
                return $move;
            }
        }

        self::fail(\sprintf('No move %s-%s', $from, $to));
    }
}
