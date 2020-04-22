<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    public function testRandomMove(): void
    {
        $chess = new Chess;
        
        $i = 0;
        while (!$chess->gameOver()) {
            ++$i;
            if ($i > 50) {
                break;
            }

            $moves = $chess->moves();
            $rnd = \random_int(0, \count($moves) - 1);
            $moveRandom = $moves[$rnd];
            $move = $chess->move(['from' => $moveRandom->from, 'to' => $moveRandom->to]);
            $this->assertNotNull($move);
        }
        
        $this->assertTrue($chess->gameOver() || $i > 50);
    }
}
