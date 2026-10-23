<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * The full state of a game at a given moment.
 * Mutable on purpose: move generation relies on in-place make/undo.
 */
final class Position
{
    /**
     * @param Board&\ArrayAccess<int, ?Piece> $board
     * @param array<string, ?int>             $kings
     * @param array<string, int>              $castling
     */
    public function __construct(
        public Board $board,
        public string $turn = Piece::WHITE,
        public array $kings = [Piece::WHITE => null, Piece::BLACK => null],
        public array $castling = [Piece::WHITE => 0, Piece::BLACK => 0],
        public ?int $epSquare = null,
        public int $halfMoves = 0,
        public int $moveNumber = 1,
    ) {
    }

    /**
     * Empty position. If a board is given, it is cleared and reused.
     */
    public static function empty(?Board $board = null): self
    {
        $board ??= new Board();
        foreach (Board::SQUARES as $square) {
            $board[$square] = null;
        }

        return new self($board);
    }
}
