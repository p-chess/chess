<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * The full state of a game at a given moment.
 * Mutable on purpose: move generation relies on in-place make/undo.
 */
final class Position
{
    private ZobristHasher $hasher;

    /** null when it must be rebuilt (e.g. after the board was edited directly) */
    private ?string $hash = null;

    /**
     * State that a Move alone cannot restore, one record per make().
     *
     * The saved hash is null once the board was edited directly: undo() then rebuilds it.
     *
     * @var list<array{Move, array<string, ?int>, array<string, int>, ?int, int, int, ?string}>
     */
    private array $undoStack = [];

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
        ?ZobristHasher $hasher = null,
    ) {
        // cloned: the hasher keeps state, so it cannot be shared between positions
        $this->hasher = null === $hasher ? new ZobristHasher() : clone $hasher;
    }

    /**
     * A clone is a snapshot: same board object, own hasher, nothing to undo.
     */
    public function __clone()
    {
        $this->hasher = clone $this->hasher;
        $this->undoStack = [];
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

    public function useHasher(ZobristHasher $hasher): void
    {
        $this->hasher = clone $hasher;
        $this->hash = null;
    }

    /**
     * Zobrist hash, kept up to date by make() and undo().
     */
    public function hash(): string
    {
        return $this->hash ??= $this->hasher->rebuild(
            $this->board,
            $this->turn,
            $this->castling,
            $this->hashableEpSquare($this->epSquare, $this->turn),
        );
    }

    /**
     * Call after changing the board or the state directly, not through make()/undo().
     */
    public function invalidateHash(): void
    {
        $this->hash = null;
        foreach ($this->undoStack as $i => $record) {
            $this->undoStack[$i][6] = null;
        }
    }

    public function inCheck(): bool
    {
        return $this->kingAttacked($this->turn);
    }

    public function kingAttacked(string $color): bool
    {
        $king = $this->kings[$color];

        return null !== $king && $this->board->isAttacked(self::swapColor($color), $king);
    }

    /**
     * Plays a move, without checking it is legal.
     */
    public function make(Move $move): void
    {
        $board = $this->board;
        $us = $this->turn;
        $them = self::swapColor($us);
        $previousHash = $this->hash();
        $previousCastling = $this->castling;
        $this->undoStack[] = [$move, $this->kings, $previousCastling, $this->epSquare, $this->halfMoves, $this->moveNumber, $previousHash];
        $hashablePreviousEpSquare = $this->hashableEpSquare($this->epSquare, $us);

        $board[$move->toSquare] = $board[$move->fromSquare];
        $board[$move->fromSquare] = null;

        // if flags:EP_CAPTURE (en passant), remove the captured pawn
        if (($move->flags & Move::BITS['EP_CAPTURE']) > 0) {
            $board[$move->toSquare + ($us === Piece::BLACK ? -16 : 16)] = null;
        }

        // if pawn promotion, replace with new piece
        if (($move->flags & Move::BITS['PROMOTION']) > 0) {
            \assert(null !== $move->promotion);
            $board[$move->toSquare] = new Piece($move->promotion, $us);
        }

        // if big pawn move, update the en passant square
        if (($move->flags & Move::BITS['BIG_PAWN']) > 0) {
            $this->epSquare = $move->toSquare + ($us === Piece::BLACK ? -16 : 16);
        } else {
            $this->epSquare = null;
        }

        // reset the 50 move counter if a pawn is moved or piece is captured
        if ($move->piece->isPawn() || ($move->flags & (Move::BITS['CAPTURE'] | Move::BITS['EP_CAPTURE'])) > 0) {
            $this->halfMoves = 0;
        } else {
            ++$this->halfMoves;
        }

        // if we moved the king
        if ($move->piece->isKing()) {
            $this->kings[$us] = $move->toSquare;

            // if we castled, move the rook next to the king
            if (($move->flags & Move::BITS['KSIDE_CASTLE']) > 0) {
                $board[$move->toSquare - 1] = $board[$move->toSquare + 1];
                $board[$move->toSquare + 1] = null;
            } elseif (($move->flags & Move::BITS['QSIDE_CASTLE']) > 0) {
                $board[$move->toSquare + 1] = $board[$move->toSquare - 2];
                $board[$move->toSquare - 2] = null;
            }

            $this->castling[$us] = 0;
        }

        // turn off castling if we move a rook
        if ($this->castling[$us] > 0) {
            foreach (Board::ROOKS[$us] as $rook) {
                if ($move->fromSquare === $rook['square'] && ($this->castling[$us] & $rook['flag']) > 0) {
                    $this->castling[$us] ^= $rook['flag'];
                    break;
                }
            }
        }

        // turn off castling if we capture a rook
        if ($this->castling[$them] > 0) {
            foreach (Board::ROOKS[$them] as $rook) {
                if ($move->toSquare === $rook['square'] && ($this->castling[$them] & $rook['flag']) > 0) {
                    $this->castling[$them] ^= $rook['flag'];
                    break;
                }
            }
        }

        if ($us === Piece::BLACK) {
            ++$this->moveNumber;
        }
        $this->turn = $them;

        $this->hash = $this->hasher->applyMove(
            $move,
            $us,
            $them,
            $hashablePreviousEpSquare,
            $this->hashableEpSquare($this->epSquare, $them),
            $previousCastling[$us],
            $this->castling[$us],
            $previousCastling[$them],
            $this->castling[$them],
        );
    }

    /**
     * The move undo() would take back, or null if there is none.
     */
    public function lastMove(): ?Move
    {
        $record = \end($this->undoStack);

        return $record === false ? null : $record[0];
    }

    /**
     * Takes back the last move played with make().
     * Returns null if there is nothing to take back.
     */
    public function undo(): ?Move
    {
        $record = \array_pop($this->undoStack);
        if ($record === null) {
            return null;
        }

        [$move, $this->kings, $this->castling, $this->epSquare, $this->halfMoves, $this->moveNumber, $previousHash] = $record;
        $this->turn = $move->turn;
        $this->unplay($move);
        $this->hash = null === $previousHash ? null : $this->hasher->restore($previousHash);

        return $move;
    }

    /**
     * Takes back a move not played with make(), restoring the state from a snapshot.
     * Used for history entries that come from outside (e.g. given to Chess's constructor).
     * Drops any pending undo records: they no longer match the board.
     */
    public function rewind(Move $move, self $before): void
    {
        $this->undoStack = [];
        $this->turn = $before->turn;
        $this->kings = $before->kings;
        $this->castling = $before->castling;
        $this->epSquare = $before->epSquare;
        $this->halfMoves = $before->halfMoves;
        $this->moveNumber = $before->moveNumber;
        $this->unplay($move);
        $this->hash = null;
    }

    /**
     * Puts the pieces back as they were before $move. The turn must already be the mover's.
     */
    private function unplay(Move $move): void
    {
        $board = $this->board;
        $us = $this->turn;
        $them = self::swapColor($us);

        // a new piece also undoes any promotion
        $board[$move->fromSquare] = null === $board[$move->toSquare] ? null : new Piece($move->piece->getType(), $move->piece->getColor());
        $board[$move->toSquare] = null;

        if (($move->flags & Move::BITS['CAPTURE']) > 0) {
            \assert(null !== $move->captured);
            $board[$move->toSquare] = new Piece($move->captured, $them);
        } elseif (($move->flags & Move::BITS['EP_CAPTURE']) > 0) {
            $board[$move->toSquare + ($us === Piece::BLACK ? -16 : 16)] = new Piece(Piece::PAWN, $them);
        }

        if (($move->flags & Move::BITS['KSIDE_CASTLE']) > 0) {
            $board[$move->toSquare + 1] = $board[$move->toSquare - 1];
            $board[$move->toSquare - 1] = null;
        } elseif (($move->flags & Move::BITS['QSIDE_CASTLE']) > 0) {
            $board[$move->toSquare - 2] = $board[$move->toSquare + 1];
            $board[$move->toSquare + 1] = null;
        }
    }

    /**
     * Returns the EP square only when a pawn of the given turn can actually
     * capture en passant, so that identical positions always hash the same.
     */
    private function hashableEpSquare(?int $epSquare, string $turn): ?int
    {
        if ($epSquare === null) {
            return null;
        }

        foreach ([2, 3] as $j) {
            $from = $epSquare - Piece::PAWN_OFFSETS[$turn][$j];
            if (($from & 0x88) === 0) {
                $piece = $this->board[$from];
                if ($piece !== null && $piece->getType() === Piece::PAWN && $piece->getColor() === $turn) {
                    return $epSquare;
                }
            }
        }

        return null;
    }

    private static function swapColor(string $color): string
    {
        return $color === Piece::WHITE ? Piece::BLACK : Piece::WHITE;
    }
}
