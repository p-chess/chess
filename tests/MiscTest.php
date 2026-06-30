<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PHPUnit\Framework\TestCase;

final class MiscTest extends TestCase
{
    public function testHistorySAN(): void
    {
        $chess = new ChessPublicator();
        $moves = ['e4','e6','d4','d5','Nc3','Nf6','Bg5','dxe4','Nxe4','Be7','Bxf6','gxf6','g3','f5','Nc3','Bf6'];

        foreach ($moves as $move) {
            self::assertNotNull($chess->move($move), $move);
        }
    }

    public function testCachedGeneratedMovesHasSAN(): void
    {
        $chess = new ChessPublicator();
        // double call to moves method needed to enable caching
        $chess->moves();
        $moves = $chess->moves();
        foreach ($moves as $move) {
            self::assertNotNull($move->san);
        }
    }

    public function testBoardHashIsCurrentAfterLoad(): void
    {
        $chess = new ChessPublicator();
        self::assertSame(
            \json_encode($chess->getBoard(), JSON_THROW_ON_ERROR),
            $chess->getBoardHash(),
            'boardHash must reflect the actual board after construction',
        );

        $fen = 'r1bqkbnr/pppp1ppp/2n5/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R w KQkq - 2 3';
        $chess2 = new ChessPublicator($fen);
        self::assertSame(
            \json_encode($chess2->getBoard(), JSON_THROW_ON_ERROR),
            $chess2->getBoardHash(),
            'boardHash must reflect the actual board after loading a non-default FEN',
        );
    }
}
