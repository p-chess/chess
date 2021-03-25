<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use Imagine\Image\AbstractImagine;
use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Output;
use PHPUnit\Framework\TestCase;

class ConstructorTest extends TestCase
{
    public function testDefaultPosition(): void
    {
        $a = new Chess();
        $b = new Chess();
        $b->load(Board::DEFAULT_POSITION);
        $output = new Output\AsciiOutput();
        self::assertEquals($output->render($a), $output->render($b));
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
     * @requires \Imagine\Image\ImagineInterface::create
     * @requires extension gd
     */
    public function testImagineOutput(): void
    {
        $dir = self::createPieceSprites();
        $chess = new Chess();
        $output = new Output\ImagineOutput(self::getImagine(), $dir);
        self::assertNotEmpty($output->render($chess));
        $chess->board->reverse();
        self::assertNotEmpty($output->render($chess));
        self::removePieceSprites($dir);
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

    private static function createPieceSprites(): string
    {
        $dir = __DIR__.'/../resources/';
        if (!\is_dir($dir)) {
            \mkdir($dir);
        }
        $pieces = ['p', 'n', 'b', 'r', 'q', 'k'];
        foreach ($pieces as $piece) {
            if (false === $imagePiece = \imagecreatetruecolor(1, 1)) {
                self::markTestIncomplete('Error in image creation');
            }
            \imagepng($imagePiece, $dir.'b'.$piece.'.png');
            \imagepng($imagePiece, $dir.'w'.$piece.'.png');
        }

        return $dir;
    }

    private static function removePieceSprites(string $dir): void
    {
        $pieces = ['p', 'n', 'b', 'r', 'q', 'k'];
        foreach (['w', 'b'] as $color) {
            foreach ($pieces as $piece) {
                \unlink($dir.$color.$piece.'.png');
            }
        }
        \rmdir($dir);
    }
}
