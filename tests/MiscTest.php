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
            (new ChessPublicator())->getBoardHash(),
            $chess->getBoardHash(),
            'boardHash must be deterministic for the same position',
        );

        $fen = 'r1bqkbnr/pppp1ppp/2n5/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R w KQkq - 2 3';
        $chess2 = new ChessPublicator($fen);
        self::assertSame(
            (new ChessPublicator($fen))->getBoardHash(),
            $chess2->getBoardHash(),
            'boardHash must be deterministic for loaded FEN positions',
        );

        $sameBoardDifferentTurn = '8/8/8/8/8/8/8/K6k b - - 0 1';
        self::assertNotSame(
            (new ChessPublicator('8/8/8/8/8/8/8/K6k w - - 0 1'))->getBoardHash(),
            (new ChessPublicator($sameBoardDifferentTurn))->getBoardHash(),
            'boardHash must include side-to-move state',
        );
    }

    public function testIncrementalHashMatchesRebuildAfterMoveAndUndo(): void
    {
        // Regular moves + castling
        $chess = new ChessPublicator();
        $moves = ['e4', 'e5', 'Nf3', 'Nc6', 'Bc4', 'Bc5', 'O-O', 'Nf6'];

        foreach ($moves as $san) {
            self::assertNotNull($chess->move($san), "move $san must be valid");
            $rebuilt = (new ChessPublicator($chess->fen()))->getBoardHash();
            self::assertSame($rebuilt, $chess->getBoardHash(), "hash drift after move $san");
        }

        foreach (\array_reverse($moves) as $san) {
            $chess->undo();
            $rebuilt = (new ChessPublicator($chess->fen()))->getBoardHash();
            self::assertSame($rebuilt, $chess->getBoardHash(), "hash drift after undo of $san");
        }

        // En passant scenario
        $epChess = new ChessPublicator('rnbqkbnr/ppp1pppp/8/3pP3/8/8/PPPP1PPP/RNBQKBNR w KQkq d6 0 3');
        $hashBeforeEp = $epChess->getBoardHash();
        $rebuilt = (new ChessPublicator($epChess->fen()))->getBoardHash();
        self::assertSame($rebuilt, $hashBeforeEp, 'hash must be correct for position with available EP');

        $epChess->move('exd6'); // en passant capture
        $rebuilt = (new ChessPublicator($epChess->fen()))->getBoardHash();
        self::assertSame($rebuilt, $epChess->getBoardHash(), 'hash drift after en passant capture');

        $epChess->undo();
        $rebuilt = (new ChessPublicator($epChess->fen()))->getBoardHash();
        self::assertSame($rebuilt, $epChess->getBoardHash(), 'hash drift after undo of en passant');
    }
}
