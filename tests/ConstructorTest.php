<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use Imagine\Image\AbstractImagine;
use PChess\Chess\Chess;
use PChess\Chess\Entry;
use PChess\Chess\History;
use PChess\Chess\Move;
use PChess\Chess\Output;
use PChess\Chess\Piece;
use PHPUnit\Framework\TestCase;

class ConstructorTest extends TestCase
{
    public function testDefaultPosition(): void
    {
        $a = new Chess();
        $b = new Chess();
        $output = new Output\AsciiOutput();
        self::assertEquals($output->render($a), $output->render($b));
    }

    public function testInvalidFen(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Chess('an invalid fen string');
    }

    public function testBuildWithHistory(): void
    {
        $move = new Move('w', 0, new Piece('p', 'w'), 100, 68);
        $history = new History();
        $entry = new Entry($move, null, ['w' => 116, 'b' => 4], ['w' => 96, 'b' => 96], null, 0, 1);
        $history->add($entry);
        $chess = new Chess('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1', $history);
        self::assertCount(1, $chess->getHistory()->getEntries());
    }

    public function testUnicodeOutput(): void
    {
        $chess = new Chess();
        $output = new Output\UnicodeOutput();
        self::assertStringContainsString('♙ ', $output->render($chess));
    }

    public function testReversedOutput(): void
    {
        $chess = new Chess();
        $output = new Output\AsciiOutput();
        self::assertStringStartsWith('   +---+---+---+---+---+---+---+---+
 8', $output->render($chess));
        $chess->board->reverse();
        self::assertStringStartsWith('   +---+---+---+---+---+---+---+---+
 1', $output->render($chess));
    }

    /**
     * @requires \Imagine\Image\ImagineInterface::create
     */
    public function testImagineOutputWithWrongSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Size must be multiple of 8, 47 given.');
        new Output\ImagineOutput(self::getImagine(), 'fakedir', 47);
    }

    /**
     * @requires extension gd
     */
    public function testImagineOutput(): void
    {
        $dir = __DIR__.'/../resources/';
        if (!\is_dir($dir)) {
            self::markTestSkipped('No resources dir found.');
        }
        $chess = new Chess();
        $output = new Output\ImagineOutput(self::getImagine(), $dir, 400, true);
        self::assertNotEmpty($output->render($chess));
        $chess->board->reverse();
        self::assertNotEmpty($output->render($chess));
    }

    public function testHtmlOutput(): void
    {
        $chess = new Chess();
        $output = new HtmlOutputStub();
        self::assertNotEmpty($output->render($chess));
    }

    public function testSvgOutput(): void
    {
        $chess = new Chess();
        $output = new Output\SvgOutput();
        self::assertStringStartsWith('<svg', $output->render($chess));
    }

    private static function getImagine(): AbstractImagine
    {
        if (\extension_loaded('gd')) {
            return new \Imagine\Gd\Imagine();
        }
        if (\class_exists('Gmagick')) {
            return new \Imagine\Imagick\Imagine();
        }
        self::markTestSkipped('No GD nor Imagick installed.');
    }
}
