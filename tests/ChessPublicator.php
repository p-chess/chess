<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\History;
use PChess\Chess\Move;
use PChess\Chess\Output\PgnOutput;

// a proxy for testing protected method
class ChessPublicator extends Chess
{
    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getLastHistory(): History
    {
        return $this->history[\count($this->history) - 1];
    }
    
    public function attackedPublic(string $color, int $square): bool
    {
        return $this->attacked($color, $square);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int, Move>
     */
    public function generateMovesPublic(array $options = []): array
    {
        return $this->generateMoves($options);
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

    public function moveToSANPublic(?Move $move): void
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

        $moves = $this->generateMoves(['legal' => false]);
        $color = $this->turn;
        foreach ($moves as $iValue) {
            $this->makeMove($iValue);

            if (!$this->kingAttacked($color)) {
                if ($depth - 1 > 0) {
                    $childs = $this->perft($depth - 1, true);
                    $nodes += $childs['nodes'];
                    $captures += $childs['captures'];
                    $enPassants += $childs['enPassants'];
                    $castles += $childs['castles'];
                    $promotions += $childs['promotions'];
                    $checks += $childs['checks'];
                    $checkmates += $childs['checkmates'];
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

    public function pgn(): string
    {
        return (new PgnOutput())->render($this);
    }
}
