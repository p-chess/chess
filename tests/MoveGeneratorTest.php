<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Fen;
use PChess\Chess\Move;
use PChess\Chess\MoveGenerator;
use PChess\Chess\Piece;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoveGeneratorTest extends TestCase
{
    #[DataProvider('pseudoLegalProvider')]
    public function testPseudoLegal(string $fen, ?string $square, int $expected): void
    {
        $generator = new MoveGenerator(
            static fn (Move $move) => self::fail('make called'),
            static fn () => self::fail('undo called'),
        );
        $position = Fen::parse($fen);
        $moves = $generator->generate($position, 'hash', null === $square ? null : Board::SQUARES[$square], false);

        self::assertCount($expected, $moves);
    }

    /**
     * @return array<string, array{string, ?string, int}>
     */
    public static function pseudoLegalProvider(): array
    {
        return [
            'initial' => [Board::DEFAULT_POSITION, null, 20],
            'single square' => [Board::DEFAULT_POSITION, 'g1', 2],
            'empty square' => [Board::DEFAULT_POSITION, 'e4', 0],
            'castling both sides' => ['r3k2r/8/8/8/8/8/8/R3K2R w KQkq - 0 1', 'e1', 7],
            'promotion' => ['8/P7/8/8/8/8/8/k1K5 w - - 0 1', 'a7', 4],
            'en passant' => ['k7/8/8/3pP3/8/8/8/K7 w - d6 0 1', 'e5', 2],
            // the pinned rook may still leave the e-file
            'pinned piece' => ['4r2k/8/8/8/8/8/4R3/4K3 w - - 0 1', null, 17],
        ];
    }

    public function testLegalFiltersThroughInjectedMakeUndo(): void
    {
        $position = Fen::parse('4r2k/8/8/8/8/8/4R3/4K3 w - - 0 1');
        $board = $position->board;
        $calls = 0;
        /** @var list<array{Move, ?Piece}> $captured */
        $captured = [];
        $generator = new MoveGenerator(
            static function (Move $move) use ($board, &$captured, &$calls): void {
                ++$calls;
                $captured[] = [$move, $board[$move->toSquare]];
                $board[$move->toSquare] = $board[$move->fromSquare];
                $board[$move->fromSquare] = null;
            },
            static function () use ($board, &$captured): void {
                $last = \array_pop($captured);
                self::assertNotNull($last);
                [$move, $piece] = $last;
                $board[$move->fromSquare] = $board[$move->toSquare];
                $board[$move->toSquare] = $piece;
            },
        );

        $moves = $generator->generate($position, 'hash');

        // rook stays on the e-file (6), king has 4 squares
        self::assertCount(10, $moves);
        self::assertSame(17, $calls);
        foreach ($moves as $move) {
            if ($move->piece->getType() === Piece::ROOK) {
                self::assertSame('e', $move->to[0]);
            }
        }
        // every make was undone
        self::assertSame('4r2k/8/8/8/8/8/4R3/4K3 w - - 0 1', Fen::serialize($position));

        // same hash: served from cache
        $calls = 0;
        self::assertSame($moves, $generator->generate($position, 'hash'));
        self::assertSame(0, $calls);
    }
}
