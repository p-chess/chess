<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Move;
use PChess\Chess\Piece;
use PHPUnit\Framework\TestCase;

class MoveTest extends TestCase
{
    public function testBuildMove(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'a2');
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e7');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'a7');
        $chess->put(new Piece(Piece::QUEEN, Piece::BLACK), 'f4');
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['a2'],
            Board::SQUARES['a4'],
            Move::BITS['NORMAL']
        ));

        self::assertEquals(Piece::PAWN, $move->piece->getType());
        self::assertSame($move->turn, $chess->turn);
        self::assertSame($move->fromSquare, Board::SQUARES['a2']);
        self::assertSame($move->toSquare, Board::SQUARES['a4']);
        self::assertEquals('a2', $move->from);
        self::assertEquals('a4', $move->to);
        self::assertSame($move->flags, Move::BITS['NORMAL']);
    }

    public function testBuildMoveWithInvalidFrom(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $chess = new Chess();
        Move::buildMove('w', $chess->board, 999, 2, 0);
    }

    public function testPutOnInvalidSquare(): void
    {
        $chess = new ChessPublicator();
        $result = $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'a9');
        self::assertFalse($result);
    }

    /**
     * @depends testBuildMove
     */
    public function testMakeMoveAndCheckHistory(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'a2');
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e7');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'a7');
        $chess->put(new Piece(Piece::QUEEN, Piece::BLACK), 'f4');
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['a2'],
            Board::SQUARES['a4'],
            Move::BITS['NORMAL']
        ));
        $chess->makeMovePublic($move);

        $lastHistory = $chess->getLastHistory();
        self::assertSame($lastHistory->move, $move);
        self::assertSame($lastHistory->turn, Piece::WHITE);
        self::assertSame($lastHistory->kings[Piece::WHITE], Board::SQUARES['e7']);
        self::assertSame($lastHistory->kings[Piece::BLACK], Board::SQUARES['a7']);
        self::assertEquals(0, $lastHistory->castling[Piece::WHITE]);
        self::assertEquals(0, $lastHistory->castling[Piece::BLACK]);
        self::assertSame($lastHistory->halfMoves, 0);
        self::assertSame($lastHistory->moveNumber, 1);

        // promotions
        $chess = new ChessPublicator('8/P7/8/8/8/8/8/K6k w - - 0 1');
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['a7'],
            Board::SQUARES['a8'],
            Move::BITS['PROMOTION'],
            Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        self::assertSame($chess->fen(), 'Q7/8/8/8/8/8/8/K6k b - - 0 1');
    }

    public function testUndoMoveAndCheckHistory(): void
    {
        $chess = new ChessPublicator(Board::EMPTY);
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'a1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'h1');
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'a7');
        $fenStart = $chess->fen();

        // normal move
        $chess = new ChessPublicator($fenStart);
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['a7'],
            Board::SQUARES['a8'],
            Move::BITS['PROMOTION'],
            Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenStart);

        // big pawn
        $chess = new ChessPublicator($fenStart);
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'd2');
        $fenStart = $chess->fen();
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['d2'],
            Board::SQUARES['d4'],
            Move::BITS['BIG_PAWN'],
            Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenStart);

        // capture
        $chess = new ChessPublicator($fenStart);
        $chess->put(new Piece(Piece::PAWN, Piece::BLACK), 'e5');
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'd4');
        $fenStart = $chess->fen();
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['d4'],
            Board::SQUARES['e5'],
            Move::BITS['CAPTURE'],
            Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenStart);

        // en passant
        $chess = new ChessPublicator($fenStart);
        $chess->put(new Piece(Piece::PAWN, Piece::BLACK), 'g4');
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'h2');
        $fenTmp = $chess->fen();
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['h2'],
            Board::SQUARES['h4'],
            Move::BITS['NORMAL']//,
            //~ Chess::QUEEN
        ));
        $chess->makeMovePublic($move);
        $fenTmp1 = $chess->fen();
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['g4'],
            Board::SQUARES['h3'],
            Move::BITS['EP_CAPTURE'],
            Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenTmp1);
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenTmp);
        $chess->remove('g4');
        $chess->remove('h2');
        self::assertSame($chess->fen(), $fenStart);

        // castling king side
        $fenTmp = 'r1bqkb1r/pppp1ppp/2n2n2/1B2p3/4P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 4 4';
        $chess = new ChessPublicator($fenTmp);
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['e1'],
            Board::SQUARES['g1'],
            Move::BITS['KSIDE_CASTLE']//,
            //~ Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        self::assertSame($chess->fen(), 'r1bqkb1r/pppp1ppp/2n2n2/1B2p3/4P3/5N2/PPPP1PPP/RNBQ1RK1 b kq - 5 4');
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenTmp);
        $chess = new ChessPublicator($fenStart);
        self::assertSame($chess->fen(), $fenStart);

        // castling queen side
        $fenTmp = 'r3kb1r/pppq1ppp/2np1n2/1B2p2b/4P3/3P1N1P/PPPB1PP1/RN1QR1K1 b kq - 2 8';
        $chess = new ChessPublicator($fenTmp);
        $move = (ChessPublicator::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['e8'],
            Board::SQUARES['c8'],
            Move::BITS['QSIDE_CASTLE']//,
            //~ Piece::QUEEN
        ));
        $chess->makeMovePublic($move);
        self::assertSame($chess->fen(), '2kr1b1r/pppq1ppp/2np1n2/1B2p2b/4P3/3P1N1P/PPPB1PP1/RN1QR1K1 w - - 3 9');
        $chess->undoMovePublic();
        self::assertSame($chess->fen(), $fenTmp);
        $chess = new ChessPublicator($fenStart);
        self::assertSame($chess->fen(), $fenStart);
    }

    public function testUndoMoveWithEmptyHistory(): void
    {
        $chess = new ChessPublicator();
        self::assertNull($chess->undoMovePublic());
    }

    public function testMoveToSAN(): void
    {
        $chess = new ChessPublicator();

        // normal pawn move
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['e2'],
            Board::SQUARES['e4'],
            Move::BITS['NORMAL']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('e4', $undo->san);
        self::assertEquals('e4', (string) $undo);

        // normal knight move
        $chess->makeMovePublic($move);
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['g8'],
            Board::SQUARES['f6'],
            Move::BITS['NORMAL']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Nf6', $undo->san);

        // normal pawn capture
        $chess = new ChessPublicator('rnbqkbnr/ppp1pppp/8/3p4/4P3/8/PPPP1PPP/RNBQKBNR w KQkq d6 0 2');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['e4'],
            Board::SQUARES['d5'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('exd5', $undo->san);

        // en passant capture
        $chess = new ChessPublicator('rnbqkbnr/ppp2ppp/8/3Pp3/8/8/PPPP1PPP/RNBQKBNR w KQkq - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['d5'],
            Board::SQUARES['e6'],
            Move::BITS['EP_CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('dxe6', $undo->san);

        // normal knight capture
        $chess = new ChessPublicator('rnbqkb1r/ppp1pppp/5n2/3P4/8/5N2/PPPP1PPP/RNBQKB1R b KQkq - 2 3');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['f6'],
            Board::SQUARES['d5'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Nxd5', $undo->san);

        // promotion
        $chess = new ChessPublicator('8/2KP4/8/5k2/8/8/8/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['d7'],
            Board::SQUARES['d8'],
            Move::BITS['PROMOTION'],
            Piece::ROOK
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('d8=R', $undo->san);

        // check
        $chess = new ChessPublicator('3R4/2K5/8/5k2/8/8/8/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['d8'],
            Board::SQUARES['f8'],
            Move::BITS['NORMAL']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Rf8+', $undo->san);

        // checkmate
        $chess = new ChessPublicator('5k2/8/1R3K2/8/8/8/8/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['b6'],
            Board::SQUARES['b8'],
            Move::BITS['NORMAL']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Rb8#', $undo->san);

        // ambiguous moves: row
        $chess = new ChessPublicator('2N2k2/8/3p4/8/2N5/8/1K6/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['c4'],
            Board::SQUARES['d6'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('N4xd6', $undo->san);

        // ambiguous moves: rank > 0 & file > 0
        $chess = new ChessPublicator('8/8/8/2qqq3/2qPq3/2qqq3/1n6/K6k b - - 0 1'); // this one is really ambiguous haha
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['d5'],
            Board::SQUARES['d4'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Qd5xd4', $undo->san);

        // ambiguous moves: col
        $chess = new ChessPublicator('5k2/8/3p4/8/2N1N3/8/1K6/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['e4'],
            Board::SQUARES['d6'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Nexd6', $undo->san);

        // ambiguous moves: col
        $chess = new ChessPublicator('5k2/8/3p4/8/2N1N3/8/1K6/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['c4'],
            Board::SQUARES['d6'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Ncxd6', $undo->san);

        // ambiguous moves: normal capture
        $chess = new ChessPublicator('5k2/8/3p2R1/8/2N5/8/1K6/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['c4'],
            Board::SQUARES['d6'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Nxd6', $undo->san);

        // ambiguous moves: normal capture
        $chess = new ChessPublicator('5k2/8/3p2R1/8/2N5/8/1K6/8 w - - 0 1');
        $move = ($chess::buildMovePublic(
            $chess->turn,
            $chess->getBoard(),
            Board::SQUARES['g6'],
            Board::SQUARES['d6'],
            Move::BITS['CAPTURE']
        ));
        $chess->makeMovePublic($move);
        $undo = $chess->undo();
        self::assertNotNull($undo);
        self::assertEquals('Rxd6', $undo->san);

        // generate moves test
        $chess = new ChessPublicator('8/ppp2P2/pkp5/ppp5/5PPP/5PKP/5PPP/8 w - - 0 1');
        $moves = $chess->generateMovesPublic();
        \array_walk($moves, static function (Move $move) use ($chess): void {
            $chess->moveToSANPublic($move);
        });
        $sans = \array_map(static function (Move $move): ?string {
            return $move->san;
        }, $moves);
        self::assertContains('f8=Q', $sans);
        self::assertContains('f8=R', $sans);
        self::assertContains('f8=B', $sans);
        self::assertContains('f8=N', $sans);
        self::assertContains('f5', $sans);
        self::assertContains('g5', $sans);
        self::assertContains('h5', $sans);
    }

    public function testSANMove(): void
    {
        // Ruy Lopez (C70)
        $chess = new ChessPublicator();
        $chess->move('e4');
        $chess->move('e5');
        $chess->move('Nf3');
        $chess->move('Nc6');
        $chess->move('Bb5');
        $chess->move('a6');
        $chess->move('Ba4');
        $chess->move('Bc5');
        self::assertSame($chess->fen(), 'r1bqk1nr/1ppp1ppp/p1n5/2b1p3/B3P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 2 5');
    }

    public function testArrayMove(): void
    {
        // Ruy Lopez (C70)
        $chess = new ChessPublicator();
        $chess->move(['from' => 'e2', 'to' => 'e4']);
        $chess->move(['from' => 'e7', 'to' => 'e5']);
        $chess->move(['from' => 'g1', 'to' => 'f3']);
        $chess->move(['from' => 'b8', 'to' => 'c6']);
        $chess->move(['from' => 'f1', 'to' => 'b5']);
        $chess->move(['from' => 'a7', 'to' => 'a6']);
        $chess->move(['from' => 'b5', 'to' => 'a4']);
        $chess->move(['from' => 'f8', 'to' => 'c5']);
        self::assertSame($chess->fen(), 'r1bqk1nr/1ppp1ppp/p1n5/2b1p3/B3P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 2 5');
    }

    /**
     * @dataProvider gameProvider
     */
    public static function testSANMoveFromRealGame(string $match, string $finalFen): void
    {
        $chess = new ChessPublicator();
        $moves = \explode(' ', $match);
        foreach ($moves as $move) {
            self::assertNotNull($chess->move($move), $move);
        }
        self::assertSame($chess->fen(), $finalFen);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function gameProvider(): array
    {
        $match1 = 'e4 e5 Nf3 Nc6 d4 exd4 Nxd4 Nf6 Nc3 Bb4 Nxc6 bxc6 Qd4 Qe7 f3 d5 Bg5 O-O O-O-O Bc5 Bxf6 gxf6 Qa4 '.
            'Be3+ Kb1 d4 Ne2 c5 Nc1 Be6 Bc4 Rfb8 Nd3 Rb6';
        $match2 = 'e4 e5 Nf3 Nc6 Bc4 Bc5 O-O d6 c3 Nf6 d4 exd4 cxd4 Bb6 Nc3 O-O h3 h6 Be3 Re8 d5 Ne5 Nxe5 dxe5 Bxb6 '.
            'axb6 f4 Qd6 fxe5 Rxe5 Qe2 Rg5 Rf3 Qc5+ Kh1 Ng4 Rg3 Ne5 Rxg5 hxg5 Bb3 g4 Rf1 Qe7 Qe3 Qh4 Qf4 Qh5 Nb5 gxh3'.
            ' Qe3 hxg2+ Kxg2 Qg6+ Kh1 Bd7 Nxc7 Rc8 d6 Kh7';
        $match3 = 'e4 e5 Nf3 Nc6 d4 exd4 Nxd4 Nf6 Nxc6 bxc6 e5 Qe7 Qe2 Nd5 c4 Nb6 Nd2 Qe6 b3 a5 Bb2 Bb4 a3 Bxd2+ '.
            'Qxd2 d5 cxd5 cxd5 Rc1 O-O Rxc7 Qg6 f3 Bf5 g4 Bb1 Bb5 Rac8 Rxc8 Rxc8 O-O h5 h3 hxg4 hxg4 Bc2 Qd4 Qe6 Rf2 '.
            'Rc7 Rh2 Nd7 b4 axb4 axb4 Nf8 Bf1 Bb3 Bd3 Bc4 Bf5 Qe7 Qd2 Rc6 Bd4 Ra6 Bb1 Ra3 Rh3 Rb3 Bc2 Qxb4 Qf2 Ng6 '.
            'e6 Rb1+ Bxb1 Qxb1+ Kh2 fxe6 Qb2 Qxb2+ Bxb2 Nf4 Rh4 Nd3 Bc3 e5 Kg3 d4 Bd2 Bd5 Rh5 Kf7 Ba5 Ke6 Rh8 Nb2 '.
            'Re8+ Kd6 Bb4+ Kc6 Rc8+ Kd7 Rc5 Ke6 Rc7 g6 Re7+ Kf6 Rd7 Ba2 Ra7 Bc4 Ba5 Bd3 f4 exf4+ Kxf4 Bc2 Ra6+ Kf7 '.
            'Ke5 Nd3+ Kxd4 Nf2 g5 Bf5 Bd2 Ke7 Kd5 Ne4 Ra7+ Ke8 Be3 Nc3+ Ke5 Kd8 Bb6+ Ke8 Rc7 Ne4 Be3 Ng3 Bf4 Nh5 Ra7 '.
            'Kf8 Bh2 Ng7 Bg1 Nh5 Bc5+ Kg8 Kd6 Kf8 Bd4 Bg4 Be5 Bf5 Rh7 Kg8 Rc7 Kf8 Kc6 Kg8 Re7 Kf8 Bd6 Kg8 Re8+ Kf7 '.
            'Re7+ Kg8 Be5 Kf8 Ra7 Bg4 Kd6 Bh3 Ra3 Bg4 Re3 Bf5 Kc7 Kf7 Kd8 Bg4 Bb2 Be6 Bc3 Bf5 Re7+ Kf8 Be5 Bd3 Ra7 '.
            'Be4 Rc7 Bb1 Bd6+ Kg8 Ke7';

        return [
            'Alekhine - Ljubimov' => [$match1, 'r5k1/p1p1qp1p/1r2bp2/2p5/Q1BpP3/3NbP2/PPP3PP/1K1R3R w - - 6 18'],
            'Zubakin - Alekhine' => [$match2, '2r5/1pNb1ppk/1p1P2q1/4n3/4P3/1B2Q3/PP6/5R1K w - - 1 31'],
            'Kasparov - Karpov' => [$match3, '6k1/2R1K3/3B2p1/6Pn/8/8/8/1b6 b - - 76 102'],
        ];
    }

    public function testGetInvalidSquare(): void
    {
        $chess = new Chess();
        self::assertNull($chess->get('b11'));
    }

    public function testInvalidConstruct(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid board code: 24');
        new Move('b', 0, new Piece('p', 'b'), 24, 1);
    }

    public function testGenerateMovesForSquare(): void
    {
        $chess = new ChessPublicator();
        $moves = $chess->generateMovesPublic(Board::SQUARES['a2'], false);
        self::assertCount(2, $moves);
    }

    public function testRemoveInvalidSquare(): void
    {
        $chess = new Chess();
        self::assertFalse($chess->remove('invalid'));
    }
}
