<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Piece;
use PHPUnit\Framework\TestCase;

class PieceTest extends TestCase
{
    public function testInvalidPieceType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid piece type');
        new Piece('z', 'w');
    }

    public function testInvalidPieceColor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid piece color');
        new Piece('p', 'y');
    }
}
