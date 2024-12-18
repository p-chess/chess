<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Piece;
use PHPUnit\Framework\TestCase;

class AttackTest extends TestCase
{
    public function testAttackedPawn(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'e4');
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']));

        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']));
        //~ self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e4']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']));

        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']));
    }

    public function testAttackedKnight(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KNIGHT, Piece::WHITE), 'e4');
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d2']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d6']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f2']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f6']));

        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d1']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d7']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d8']));

        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f1']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f7']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f8']));
    }

    public function testAttackedBishop(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::BISHOP, Piece::WHITE), 'e4');
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']));

        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']));
    }

    public function testAttackedRook(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::ROOK, Piece::WHITE), 'e4');
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f7']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f8']));

        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']));
    }

    public function testAttackedQueen(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::QUEEN, Piece::WHITE), 'e4');
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']));
    }

    public function testAttackedKing(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e4');
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']));
        self::assertFalse($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']));
        self::assertTrue($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']));
    }

    public function testInCheck(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e7');
        $chess->put(new Piece(Piece::QUEEN, Piece::BLACK), 'e4');
        self::assertSame($chess->turn, Piece::WHITE);
        self::assertTrue($chess->inCheck());

        $chess->remove('e7');
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'f7');
        self::assertFalse($chess->inCheck());
    }

    public function testInCheckmate(): void
    {
        $chess = new ChessPublicator('r1bqk1nr/pppp1Qpp/2n5/2b1p3/2B1P3/8/PPPP1PPP/RNB1K1NR b KQkq - 0 4');
        self::assertTrue($chess->inCheckmate());
    }

    public function testInStalemate(): void
    {
        // fen source: https://www.redhotpawn.com/forum/only-chess/interesting-stalemate-position.152109
        // start fen : 3b3k/p6p/1p5P/3q4/8/n7/PP6/K4Q2 w - - 0 1
        $chess = new ChessPublicator('7k/p6p/1p3b1P/3q4/8/n7/PP6/K7 w - - 0 2');
        self::assertTrue($chess->inStalemate());
    }

    public function testInsufficientMaterial(): void
    {
        // k vs k
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        self::assertTrue($chess->insufficientMaterial());

        // k vs kn
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $chess->put(new Piece(Piece::KNIGHT, Piece::WHITE), 'e4');
        self::assertTrue($chess->insufficientMaterial());

        // k vs kb
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $chess->put(new Piece(Piece::BISHOP, Piece::WHITE), 'e4');
        self::assertTrue($chess->insufficientMaterial());

        // k vs k(b){0,} << bishop(s) in same color
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'e5');
        self::assertTrue($chess->insufficientMaterial());
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'd6');
        self::assertTrue($chess->insufficientMaterial());
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'c7');
        self::assertTrue($chess->insufficientMaterial());
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'b8');
        self::assertTrue($chess->insufficientMaterial());
    }

    public function testInThreefoldRepetition(): void
    {
        $chess = new ChessPublicator();

        /*
         * [Event "Fischer - Petrosian Candidates Final"]
         * [Site "Buenos Aires ARG"]
         * [Date "1971.10.07"]
         * [EventDate "1971.09.30"]
         * [Round "3"]
         * [Result "1/2-1/2"]
         * [White "Robert James Fischer"]
         * [Black "Tigran Vartanovich Petrosian"]
         * [ECO "C11"]
         * [WhiteElo "?"]
         * [BlackElo "?"]
         * [PlyCount "67"]
         */
        $match = 'e4 e6 d4 d5 Nc3 Nf6 Bg5 dxe4 Nxe4 Be7 Bxf6 gxf6 g3 f5 Nc3 Bf6 Nge2 Nc6 d5 exd5 Nxd5 Bxb2 Bg2 '.
            'O-O O-O Bh8 Nef4 Ne5 Qh5 Ng6 Rad1 c6 Ne3 Qf6 Kh1 Bg7 Bh3 Ne7 Rd3 Be6 Rfd1 Bh6 Rd4 Bxf4 Rxf4 Rad8 '.
            'Rxd8 Rxd8 Bxf5 Nxf5 Nxf5 Rd5 g4 Bxf5 gxf5 h6 h3 Kh7 Qe2 Qe5 Qh5 Qf6 Qe2 Re5 Qd3 Rd5 Qe2';
        $moves = \explode(' ', \trim($match));
        foreach ($moves as $move) {
            self::assertNotNull($chess->move($move), $move);
        }

        self::assertTrue($chess->inThreefoldRepetition());
    }
}
