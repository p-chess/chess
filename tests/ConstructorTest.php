<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

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
        $this->assertEquals($output->render($a), $output->render($b));
        $b->reset();
        $this->assertEquals($output->render($a), $output->render($b));
    }

    public function testUnicodeOutput(): void
    {
        $chess = new Chess();
        $output = new Output\UnicodeOutput();
        $this->assertStringContainsString('♙ ', $output->render($chess));
    }

    public function testReversedeOutput(): void
    {
        $chess = new Chess();
        $output = new Output\AsciiOutput();
        $this->assertStringStartsWith('   +---+---+---+---+---+---+---+---+
 8', $output->render($chess));
        $chess->board->reverse();
        $this->assertStringStartsWith('   +---+---+---+---+---+---+---+---+
 1', $output->render($chess));
    }
}
