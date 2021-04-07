<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use Imagine\Image\AbstractImagine;
use PChess\Chess\Chess;
use PChess\Chess\Output;
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

    public function testUnicodeOutput(): void
    {
        $chess = new Chess();
        $output = new Output\UnicodeOutput();
        self::assertStringContainsString('♙ ', $output->render($chess));
    }

    public function testReversedeOutput(): void
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
