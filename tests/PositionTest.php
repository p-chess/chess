<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Fen;
use PChess\Chess\Move;
use PChess\Chess\MoveGenerator;
use PChess\Chess\Position;
use PChess\Chess\ZobristHasher;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    public function testSharedHasherViaConstructor(): void
    {
        $hasher = new ZobristHasher();
        $a = self::withHasher(Fen::parse(Board::DEFAULT_POSITION), $hasher);
        $b = self::withHasher(Fen::parse(Board::DEFAULT_POSITION), $hasher);

        self::assertSameHashAfterE4($a, $b);
    }

    public function testSharedHasherViaUseHasher(): void
    {
        $hasher = new ZobristHasher();
        $a = Fen::parse(Board::DEFAULT_POSITION);
        $b = Fen::parse(Board::DEFAULT_POSITION);
        $a->useHasher($hasher);
        $b->useHasher($hasher);

        self::assertSameHashAfterE4($a, $b);
    }

    private static function withHasher(Position $position, ZobristHasher $hasher): Position
    {
        return new Position(
            $position->board,
            $position->turn,
            $position->kings,
            $position->castling,
            $position->epSquare,
            $position->halfMoves,
            $position->moveNumber,
            $hasher,
        );
    }

    private static function assertSameHashAfterE4(Position $a, Position $b): void
    {
        // picked first: generating moves runs make/undo, which would resync the hasher
        $moveA = self::e4($a);
        $moveB = self::e4($b);
        $a->hash();
        $b->hash();
        $a->make($moveA);
        $b->make($moveB);

        $expected = Fen::parse('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq - 0 1')->hash();
        self::assertSame($expected, $a->hash());
        self::assertSame($expected, $b->hash());
    }

    private static function e4(Position $position): Move
    {
        foreach ((new MoveGenerator())->generate($position) as $move) {
            if ($move->from === 'e2' && $move->to === 'e4') {
                return $move;
            }
        }

        self::fail('e2-e4 not generated');
    }
}
