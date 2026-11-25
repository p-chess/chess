<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PChess\Chess\Fen;
use PChess\Chess\Move;
use PChess\Chess\MoveGenerator;
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

    public function testEncodeTakesBackCheckProbe(): void
    {
        // white rook to f8 gives check: Rf8+
        $fen = '3R4/2K5/8/5k2/8/8/8/8 w - - 0 1';
        $position = Fen::parse($fen);
        $move = self::find((new MoveGenerator())->generate($position), 'd8', 'f8');

        $encoder = new SanEncoder();
        self::assertSame('Rf8+', $encoder->encode($move, $position));
        // the check probe is taken back
        self::assertSame($fen, Fen::serialize($position));
        self::assertSame(Fen::parse($fen)->hash(), $position->hash());
    }

    public function testCheckmateSuffix(): void
    {
        // back-rank mate: Rd8#
        $position = Fen::parse('6k1/5ppp/8/8/8/8/8/3R2K1 w - - 0 1');
        $move = self::find((new MoveGenerator())->generate($position), 'd1', 'd8');
        self::assertSame('Rd8#', (new SanEncoder())->encode($move, $position));
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
