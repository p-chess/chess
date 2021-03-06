<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PHPUnit\Framework\TestCase;

class MiscTest extends TestCase
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
}
