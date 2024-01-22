<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Entry;
use PChess\Chess\Move;

// a proxy for testing protected method
class ChessPublicator extends Chess
{
    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getLastHistory(): Entry
    {
        return $this->history->get(\count($this->history->getEntries()) - 1);
    }

    public function attackedPublic(string $color, int $square): bool
    {
        return $this->attacked($color, $square);
    }

    /**
     * @return array<int, Move>
     */
    public function generateMovesPublic(int $square = null, bool $legal = true): array
    {
        return $this->generateMoves($square, $legal);
    }

    public static function buildMovePublic(
        string $turn,
        Board $board,
        int $from,
        int $to,
        int $flags,
        ?string $promotion = null
    ): Move {
        return Move::buildMove($turn, $board, $from, $to, $flags, $promotion);
    }

    public function makeMovePublic(Move $move): void
    {
        $this->makeMove($move);
    }

    public function undoMovePublic(): ?Move
    {
        return $this->undoMove();
    }

    public function moveToSANPublic(Move $move): void
    {
        $this->moveToSAN($move);
    }

    /**
     * @return array<string, mixed>|int
     */
    public function perft(int $depth, bool $full = false)
    {
        $nodes = 0;
        $captures = 0;
        $enPassants = 0;
        $castles = 0;
        $promotions = 0;
        $checks = 0;
        $checkmates = 0;

        $moves = $this->generateMoves(null, false);
        $color = $this->turn;
        foreach ($moves as $iValue) {
            $this->makeMove($iValue);

            if (!$this->kingAttacked($color)) {
                if ($depth - 1 > 0) {
                    $children = $this->perft($depth - 1, true);
                    if (!\is_array($children)) {
                        continue;
                    }
                    $nodes += $children['nodes'];
                    $captures += $children['captures'];
                    $enPassants += $children['enPassants'];
                    $castles += $children['castles'];
                    $promotions += $children['promotions'];
                    $checks += $children['checks'];
                    $checkmates += $children['checkmates'];
                } else {
                    ++$nodes;
                }
            }
            $this->undoMove();
        }

        if ($full === false) {
            return $nodes;
        }

        return \compact('nodes', 'captures', 'enPassants', 'castles', 'promotions', 'checks', 'checkmates');
    }
}
