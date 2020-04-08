<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Piece;
use PHPUnit\Framework\TestCase;

class AttackTest extends TestCase
{
    public function testAttackedPawn(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::PAWN, Piece::WHITE), 'e4');
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']), false);
        
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']), false);
        //~ $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e4']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']), false);
        
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']), true);
    }
    
    public function testAttackedKnight(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::KNIGHT, Piece::WHITE), 'e4');
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d2']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d6']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f2']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f6']), true);
        
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d1']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d7']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d8']), false);
        
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f1']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f7']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f8']), false);
    }
    
    public function testAttackedBishop(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::BISHOP, Piece::WHITE), 'e4');
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']), true);
        
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']), false);
    }
    
    public function testAttackedRook(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::ROOK, Piece::WHITE), 'e4');
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f7']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f8']), false);

        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']), true);
    }
    
    public function testAttackedQueen(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::QUEEN, Piece::WHITE), 'e4');
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e4']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']), true);
    }
    
    public function testAttackedKing(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e4');
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d4']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['d5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e4']), false);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['e5']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f3']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f4']), true);
        $this->assertSame($chess->attackedPublic(Piece::WHITE, Board::SQUARES['f5']), true);
    }
    
    public function testInCheck(): void
    {
        $chess = new ChessPublicator();
        $chess->clear();
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e7');
        $chess->put(new Piece(Piece::QUEEN, Piece::BLACK), 'e4');
        $this->assertSame($chess->turn(), Piece::WHITE);
        $this->assertSame($chess->inCheck(), true);
        
        $chess->remove('e7');
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'f7');
        $this->assertSame($chess->inCheck(), false);
    }
    
    public function testInCheckmate(): void
    {
        $chess = new ChessPublicator();
        $this->assertSame($chess->inCheckmate(), false);
        
        $chess->load('r1bqk1nr/pppp1Qpp/2n5/2b1p3/2B1P3/8/PPPP1PPP/RNB1K1NR b KQkq - 0 4');
        $this->assertSame($chess->inCheckmate(), true);
    }
    
    public function testInStalemate(): void
    {
        $chess = new ChessPublicator();
        $this->assertSame($chess->inStalemate(), false);
        
        // fen source: https://www.redhotpawn.com/forum/only-chess/interesting-stalemate-position.152109
        // start fen : 3b3k/p6p/1p5P/3q4/8/n7/PP6/K4Q2 w - - 0 1
        $chess->load('7k/p6p/1p3b1P/3q4/8/n7/PP6/K7 w - - 0 2');
        $this->assertSame($chess->inStalemate(), true);
    }
    
    public function testInsufficientMaterial(): void
    {
        $chess = new ChessPublicator();
        $this->assertSame($chess->insufficientMaterial(), false);
        
        // k vs k
        $chess->clear();
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $this->assertSame($chess->insufficientMaterial(), true);
        
        // k vs kn
        $chess->clear();
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $chess->put(new Piece(Piece::KNIGHT, Piece::WHITE), 'e4');
        $this->assertSame($chess->insufficientMaterial(), true);
        
        // k vs kb
        $chess->clear();
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $chess->put(new Piece(Piece::BISHOP, Piece::WHITE), 'e4');
        $this->assertSame($chess->insufficientMaterial(), true);
        
        // k vs k(b){0,} << bishop(s) in same color
        $chess->clear();
        $chess->put(new Piece(Piece::KING, Piece::WHITE), 'e1');
        $chess->put(new Piece(Piece::KING, Piece::BLACK), 'e8');
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'e5');
        $this->assertSame($chess->insufficientMaterial(), true);
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'd6');
        $this->assertSame($chess->insufficientMaterial(), true);
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'c7');
        $this->assertSame($chess->insufficientMaterial(), true);
        $chess->put(new Piece(Piece::BISHOP, Piece::BLACK), 'b8');
        $this->assertSame($chess->insufficientMaterial(), true);
    }
    
    public function testinThreefoldRepetition(): void
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
        $match = '1. e4 e6 2. d4 d5 3. Nc3 Nf6 4. Bg5 dxe4 5. Nxe4 Be7 6. Bxf6
		gxf6 7. g3 f5 8. Nc3 Bf6 9. Nge2 Nc6 10. d5 exd5 11. Nxd5 Bxb2
		12. Bg2 O-O 13. O-O Bh8 14. Nef4 Ne5 15. Qh5 Ng6 16. Rad1 c6
		17. Ne3 Qf6 18. Kh1 Bg7 19. Bh3 Ne7 20. Rd3 Be6 21. Rfd1 Bh6
		22. Rd4 Bxf4 23. Rxf4 Rad8 24. Rxd8 Rxd8 25. Bxf5 Nxf5
		26. Nxf5 Rd5 27. g4 Bxf5 28. gxf5 h6 29. h3 Kh7 30. Qe2 Qe5
		31. Qh5 Qf6 32. Qe2 Re5 33.Qd3 Rd5 34.Qe2';
        $moves = preg_replace("/([0-9]{0,})\./", '', $match);
        $moves = str_replace('  ', ' ', str_replace("\r", ' ', str_replace("\n", ' ', str_replace("\t", '', $moves))));
        $moves = explode(' ', trim($moves));
        foreach ($moves as $move) {
            $this->assertNotNull($chess->move($move), $move);
        }
        
        $this->assertSame($chess->inThreefoldRepetition(), true);
    }
}
