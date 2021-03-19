<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Validation;
use PHPUnit\Framework\TestCase;

class PgnTest extends TestCase
{
    public function testClear(): void
    {
        // with clear
        $chess = new ChessPublicator();
        $chess->clear();
        self::assertSame($chess->pgn(), '');

        // without clear
        $chess = new ChessPublicator();
        self::assertSame($chess->pgn(), '');
    }

    public function testNormal(): void
    {
        $chess = new ChessPublicator();
        $chess->header('White', 'John');
        $chess->header('Black', 'Cena');

        $match = '1. e4 e6 2. d4 d5 3. Nc3 Nf6 4. Bg5 dxe4 5. Nxe4 Be7 6. Bxf6
				gxf6 7. g3 f5 8. Nc3 Bf6';
        $moves = \preg_replace("/(\d*)\./", '', $match);
        $moves = \str_replace(["\t", "\n", "\r", '  '], ['', ' ', ' ', ' '], $moves);
        $moves = \explode(' ', \trim($moves));
        foreach ($moves as $move) {
            $chess->move($move);
        }

        $fen = $chess->fen();
        $chess->header('FEN', $fen);
        $pgn = $chess->pgn();

        // check setup ok
        self::assertStringContainsString('[White "John"]', $pgn);
        self::assertStringContainsString('[Black "Cena"]', $pgn);
        self::assertStringContainsString('[FEN "'.$fen.'"]', $pgn);

        // check movements
        self::assertStringContainsString('1. e4 e6', $pgn);
        self::assertStringContainsString('2. d4 d5', $pgn);
        self::assertStringContainsString('3. Nc3 Nf6', $pgn);
        self::assertStringContainsString('4. Bg5 dxe4', $pgn);
        self::assertStringContainsString('5. Nxe4 Be7', $pgn);
        self::assertStringContainsString('8. Nc3 Bf6', $pgn);
    }

    public function testBlackFirst(): void
    {
        $chess = new ChessPublicator();
        $chess->move('e4');
        $fen = $chess->fen();

        $chess->load($fen); // do setup with black first
        $chess->move('e5');
        $chess->move('Nf3');
        $chess->move('Nc6');

        //~ $pgn = $chess->pgn([ 'max_width' => 40, 'new_line' => PHP_EOL ]);
        $pgn = $chess->pgn();

        // check setup ok
        self::assertStringContainsString('[SetUp "1"]', $pgn);
        self::assertStringContainsString('[FEN "'.$fen.'"]', $pgn);

        // check movements
        self::assertStringContainsString('1. ... e5', $pgn);
        self::assertStringContainsString('2. Nf3 Nc6', $pgn);
    }

    public function testParsePgn(): void
    {
        $parsed = Validation::parsePgn('1.e4 e5 2.Nf3');
        self::assertContains('e4', $parsed['moves']);
        self::assertContains('e5', $parsed['moves']);
        self::assertContains('Nf3', $parsed['moves']);

        $parsed = Validation::parsePgn(
            <<<EOD
[Event "Earl tourn"]
[Site "?"]
1.e4 e5 2.Nf3
EOD
        );

        self::assertEquals(['Event' => 'Earl tourn', 'Site' => '?'], $parsed['header']);
        self::assertContains('e4', $parsed['moves']);
        self::assertContains('e5', $parsed['moves']);
        self::assertContains('Nf3', $parsed['moves']);
    }

    /**
     * @depends testParsePgn
     */
    public function testValidatePgn(): void
    {
        $parsed = Validation::validatePgn('1.e4 e5some failed string 2.Nf3');
        self::assertFalse($parsed);

        $parsed = Validation::validatePgn(
            <<<EOD
[Event "Earl tourn"]
[Site "?"]
1.e4 e5 2.Nf3 oke failed
EOD
        );
        self::assertFalse($parsed);

        $parsed = Validation::validatePgn(
            <<<EOD
[Event "Earl tourn"]
oke failed here
[Site "?"]
1.e4 e5 2.Nf3
EOD
        );
        self::assertFalse($parsed);

        $parsed = Validation::validatePgn(
            <<<EOD
[Event "Earl tourn"]
[Site "?"]
1.e4 e5 2.Nf3 1-0
EOD
        );
        self::assertTrue($parsed);

        $parsed = Validation::validatePgn(
            <<<EOD
[Event "Earl tourn"]
[Site "?"]
1.e4 e5 2.Nf3 1/2-1/2
EOD
        );
        self::assertTrue($parsed);

        $parsed = Validation::validatePgn('1.e4 e5 2.Nf3', ['verbose' => true]);
        if (!\is_array($parsed)) {
            throw new \RuntimeException('Invalid result from validatePgn call.');
        }
        self::assertContains('e4', $parsed['moves']);
        self::assertContains('e5', $parsed['moves']);
        self::assertContains('Nf3', $parsed['moves']);
        self::assertSame($parsed['game']->fen(), 'rnbqkbnr/pppp1ppp/8/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2');

        $parsed = Validation::validatePgn(<<<EOD
[Event "Earl tourn"]
[Site "?"]
1.e4 e5 2.Nf3
EOD
        , ['verbose' => true]);
        if (!\is_array($parsed)) {
            self::markTestSkipped();
        }
        self::assertEquals(['Event' => 'Earl tourn', 'Site' => '?'], $parsed['header']);
        self::assertContains('e4', $parsed['moves']);
        self::assertContains('e5', $parsed['moves']);
        self::assertContains('Nf3', $parsed['moves']);
        self::assertSame($parsed['game']->fen(), 'rnbqkbnr/pppp1ppp/8/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2');
    }
}
