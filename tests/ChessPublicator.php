<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Entry;
use PChess\Chess\Move;

// a proxy for testing protected method
final class ChessPublicator extends Chess
{
    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getBoardHash(): string
    {
        return $this->boardHash;
    }

    public function getLastHistory(): Entry
    {
        return $this->history->get(\count($this->history->getEntries()) - 1);
    }

    /**
     * @return array<int, Move>
     */
    public function generateMovesPublic(?int $square = null, bool $legal = true): array
    {
        return $this->generateMoves($square, $legal);
    }

    public static function buildMovePublic(
        string $turn,
        Board $board,
        int $from,
        int $to,
        int $flags,
        ?string $promotion = null,
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
     * @return array<string, int>|int
     */
    public function perft(int $depth, bool $full = false): array|int
    {
        $result = $this->countNodes($depth, $full);

        return $full ? $result : $result['nodes'];
    }

    /**
     * @return array<string, int>
     */
    private function countNodes(int $depth, bool $full): array
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
        foreach ($moves as $move) {
            $this->makeMove($move);

            if (!$this->kingAttacked($color)) {
                if ($depth - 1 > 0) {
                    $children = $this->countNodes($depth - 1, $full);
                    $nodes += $children['nodes'];
                    $captures += $children['captures'];
                    $enPassants += $children['enPassants'];
                    $castles += $children['castles'];
                    $promotions += $children['promotions'];
                    $checks += $children['checks'];
                    $checkmates += $children['checkmates'];
                } elseif (!$full) {
                    // node-only fast path: skip leaf statistics (check detection is expensive)
                    ++$nodes;
                } else {
                    ++$nodes;
                    if (($move->flags & (Move::BITS['CAPTURE'] | Move::BITS['EP_CAPTURE'])) > 0) {
                        ++$captures;
                    }
                    if (($move->flags & Move::BITS['EP_CAPTURE']) > 0) {
                        ++$enPassants;
                    }
                    if (($move->flags & (Move::BITS['KSIDE_CASTLE'] | Move::BITS['QSIDE_CASTLE'])) > 0) {
                        ++$castles;
                    }
                    if (($move->flags & Move::BITS['PROMOTION']) > 0) {
                        ++$promotions;
                    }
                    if ($this->inCheck()) {
                        ++$checks;
                        if ($this->inCheckmate()) {
                            ++$checkmates;
                        }
                    }
                }
            }
            $this->undoMove();
        }

        return \compact('nodes', 'captures', 'enPassants', 'castles', 'promotions', 'checks', 'checkmates');
    }
}
