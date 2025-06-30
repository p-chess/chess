<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase
{
    /**
     * TODO re-check this test.
     * This test has a failure percentage around 10%, sometime the move is not good (null move),
     * some other times (more often) the game is not ended after the loop.
     */
    public function testRandomMove(): void
    {
        $chess = new Chess();

        $i = 0;
        while (!$chess->gameOver()) {
            ++$i;
            if ($i > 50) {
                break;
            }

            $moves = $chess->moves();
            if (($movesNumber = \count($moves)) < 1) {
                break;
            }
            $rnd = $movesNumber > 1 ? \random_int(0, $movesNumber - 1) : 0;
            $moveRandom = $moves[$rnd];
            $move = $chess->move(['from' => $moveRandom->from, 'to' => $moveRandom->to]);
            self::assertNotNull($move, 'Invalid move. Moves left '.$movesNumber);
        }

        self::assertTrue($chess->gameOver() || $i > 50, 'Game prematurely ended at move '.$i);
    }

    public function testInvalidMove(): void
    {
        $chess = new Chess();
        self::assertNull($chess->move('yxz'));
    }

    public function testGetHistory(): void
    {
        $chess = new Chess();
        $chess->move('e4');
        self::assertEquals('e4', $chess->getHistory()->get(0)->move->san);
        self::assertEquals('e4', $chess->getHistory()->get(0));
    }
}
