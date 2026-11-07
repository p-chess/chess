<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * Converts moves to Standard Algebraic Notation.
 *
 * It needs the legal moves of the current position (for disambiguation)
 * and a way to play a move and take it back (for the check suffix).
 * Both are injected, so the encoder holds no reference to Chess.
 */
final class SanEncoder
{
    /** @var array<string, string> */
    private array $cache = [];

    /**
     * @param \Closure(): array<int, Move> $generateMoves legal moves for the side to move
     * @param \Closure(Move): void         $makeMove      plays a move on the current position
     * @param \Closure(): mixed            $undoMove      takes back the last move
     * @param \Closure(): bool             $inCheck       whether the side to move is in check
     */
    public function __construct(
        private readonly \Closure $generateMoves,
        private readonly \Closure $makeMove,
        private readonly \Closure $undoMove,
        private readonly \Closure $inCheck,
    ) {
    }

    /**
     * @param string $hash hash of the current position, used as cache key
     */
    public function encode(Move $move, string $hash): string
    {
        $cacheKey = \json_encode($move, JSON_THROW_ON_ERROR).$hash;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $output = '';
        if (($move->flags & Move::BITS['KSIDE_CASTLE']) > 0) {
            $output = 'O-O';
        } elseif (($move->flags & Move::BITS['QSIDE_CASTLE']) > 0) {
            $output = 'O-O-O';
        } else {
            // pawn e2->e4 is "e4", knight g8->f6 is "Nf6"
            if (!$move->piece->isPawn()) {
                $output .= \strtoupper($move->piece->getType()).self::disambiguator($move, ($this->generateMoves)());
            }

            // x on capture
            if (($move->flags & (Move::BITS['CAPTURE'] | Move::BITS['EP_CAPTURE'])) > 0) {
                // pawn e5->d6 is "exd6"
                if ($move->piece->isPawn()) {
                    $output .= $move->from[0];
                }

                $output .= 'x';
            }

            $output .= $move->to;

            // promotion example: e8=Q
            if (($move->flags & Move::BITS['PROMOTION']) > 0) {
                \assert(null !== $move->promotion);
                $output .= '='.\strtoupper($move->promotion);
            }
        }

        // check / checkmate
        ($this->makeMove)($move);
        if (($this->inCheck)()) {
            $output .= \count(($this->generateMoves)()) === 0 ? '#' : '+';
        }
        ($this->undoMove)();

        return $this->cache[$cacheKey] = $output;
    }

    /**
     * Uniquely identifies ambiguous moves.
     *
     * @param array<int, Move> $moves legal moves of the position $move is played from
     */
    public static function disambiguator(Move $move, array $moves): string
    {
        $from = $move->fromSquare;
        $to = $move->toSquare;
        $pieceType = $move->piece->getType();

        $ambiguities = 0;
        $sameRank = 0;
        $sameFile = 0;

        foreach ($moves as $aMove) {
            $ambiguityFrom = $aMove->fromSquare;
            $ambiguityTo = $aMove->toSquare;
            $ambiguityPieceType = $aMove->piece->getType();

            /* if a move of the same piece type ends on the same to square, we'll
             * need to add a disambiguator to the algebraic notation
             */
            if ($pieceType === $ambiguityPieceType && $from !== $ambiguityFrom && $to === $ambiguityTo) {
                ++$ambiguities;
                if (Board::rank($from) === Board::rank($ambiguityFrom)) {
                    ++$sameRank;
                }
                if (Board::file($from) === Board::file($ambiguityFrom)) {
                    ++$sameFile;
                }
            }
        }

        if ($ambiguities > 0) {
            /* if there exists a similar moving piece on the same rank and file as
             * the move in question, use the square as the disambiguator
             */
            if ($sameRank > 0 && $sameFile > 0) {
                return Board::algebraic($from);
            }

            /* if the moving piece rests on the same file, use the rank symbol as the
             * disambiguator
             */
            if ($sameFile > 0) {
                return \substr(Board::algebraic($from), 1, 1);
            }

            // else use the file symbol
            return \substr(Board::algebraic($from), 0, 1);
        }

        return '';
    }
}
