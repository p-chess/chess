<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * Generates moves for the side to move.
 *
 * Legality is checked by playing each pseudo-legal move and taking it back,
 * so make/undo are injected. The position is read, never copied:
 * in-place make/undo is what keeps generation fast.
 */
final class MoveGenerator
{
    private const SECOND_RANK = [Piece::BLACK => Board::RANK_7, Piece::WHITE => Board::RANK_2];

    /** @var array<string, array<int, Move>> */
    private array $cache = [];

    /**
     * @param \Closure(Move): void $makeMove plays a move on the position passed to generate()
     * @param \Closure(): mixed    $undoMove takes back the last move
     */
    public function __construct(
        private readonly \Closure $makeMove,
        private readonly \Closure $undoMove,
    ) {
    }

    /**
     * @param string $hash hash of the position, used as cache key
     *
     * @return array<int, Move>
     */
    public function generate(Position $position, string $hash, ?int $square = null, bool $legal = true): array
    {
        $cacheKey = $hash.'|'.($square === null ? 'all' : (string) $square).'|'.($legal ? '1' : '0');
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $moves = self::pseudoLegal($position, $square);
        if ($legal) {
            $moves = $this->filterLegal($position, $moves);
        }

        return $this->cache[$cacheKey] = $moves;
    }

    /**
     * All moves, including those that leave the king in check.
     *
     * @return array<int, Move>
     */
    private static function pseudoLegal(Position $position, ?int $square): array
    {
        $moves = [];
        $board = $position->board;
        $us = $position->turn;
        $them = $us === Piece::WHITE ? Piece::BLACK : Piece::WHITE;

        if (null !== $square) {
            $firstSquare = $lastSquare = $square;
            $singleSquare = true;
        } else {
            $firstSquare = Board::SQUARES['a8'];
            $lastSquare = Board::SQUARES['h1'];
            $singleSquare = false;
        }

        for ($i = $firstSquare; $i <= $lastSquare; ++$i) {
            if (($i & 0x88) > 0) {
                $i += 7;
                continue;
            } // check edge of board

            $piece = $board[$i];
            if ($piece === null || $piece->getColor() !== $us) {
                continue;
            }

            if ($piece->isPawn()) {
                // single square, non-capturing
                $to = $i + Piece::PAWN_OFFSETS[$us][0];
                if ($board[$to] === null) {
                    self::addMove($us, $board, $moves, $i, $to, Move::BITS['NORMAL']);

                    // double square
                    $to = $i + Piece::PAWN_OFFSETS[$us][1];
                    if (self::SECOND_RANK[$us] === Board::rank($i) && $board[$to] === null) {
                        self::addMove($us, $board, $moves, $i, $to, Move::BITS['BIG_PAWN']);
                    }
                }

                // pawn captures
                for ($j = 2; $j < 4; ++$j) {
                    $to = $i + Piece::PAWN_OFFSETS[$us][$j];
                    if (($to & 0x88) > 0) {
                        continue;
                    }
                    if ($board[$to] !== null) {
                        if ($board[$to]->getColor() === $them) {
                            self::addMove($us, $board, $moves, $i, $to, Move::BITS['CAPTURE']);
                        }
                    } elseif ($to === $position->epSquare) {
                        self::addMove($us, $board, $moves, $i, $to, Move::BITS['EP_CAPTURE']);
                    }
                }
            } else {
                foreach (Piece::OFFSETS[$piece->getType()] as $offset) {
                    $to = $i;

                    while (true) {
                        $to += $offset;
                        if (($to & 0x88) > 0) {
                            break;
                        }

                        if ($board[$to] === null) {
                            self::addMove($us, $board, $moves, $i, $to, Move::BITS['NORMAL']);
                        } else {
                            if ($board[$to]->getColor() === $us) {
                                break;
                            }
                            self::addMove($us, $board, $moves, $i, $to, Move::BITS['CAPTURE']);
                            break;
                        }

                        if ($piece->isKnight() || $piece->isKing()) {
                            break;
                        }
                    }
                }
            }
        }

        // castling
        // a) we're generating all moves
        // b) we're doing single square move generation on king's square
        $king = $position->kings[$us];
        if (!$singleSquare || $lastSquare === $king) {
            if (($position->castling[$us] & Move::BITS['KSIDE_CASTLE']) > 0) {
                \assert(null !== $king);
                $castlingTo = $king + 2;

                if (
                    $board[$king + 1] === null &&
                    $board[$castlingTo] === null &&
                    !$board->isAttacked($them, $king) &&
                    !$board->isAttacked($them, $king + 1) &&
                    !$board->isAttacked($them, $castlingTo)
                ) {
                    self::addMove($us, $board, $moves, $king, $castlingTo, Move::BITS['KSIDE_CASTLE']);
                }
            }

            if (($position->castling[$us] & Move::BITS['QSIDE_CASTLE']) > 0) {
                \assert(null !== $king);
                $castlingTo = $king - 2;

                if (
                    $board[$king - 1] === null &&
                    $board[$king - 2] === null && // $castlingTo
                    $board[$king - 3] === null && // col "b", next to rook
                    !$board->isAttacked($them, $king) &&
                    !$board->isAttacked($them, $king - 1) &&
                    !$board->isAttacked($them, $castlingTo)
                ) {
                    self::addMove($us, $board, $moves, $king, $castlingTo, Move::BITS['QSIDE_CASTLE']);
                }
            }
        }

        return $moves;
    }

    /**
     * Drops moves that leave our king attacked.
     * The board and king square are read up front: undo may swap the
     * Position instance, but the Board is always the same object.
     *
     * @param array<int, Move> $moves
     *
     * @return array<int, Move>
     */
    private function filterLegal(Position $position, array $moves): array
    {
        $board = $position->board;
        $us = $position->turn;
        $them = $us === Piece::WHITE ? Piece::BLACK : Piece::WHITE;
        $king = $position->kings[$us];

        $legalMoves = [];
        foreach ($moves as $move) {
            ($this->makeMove)($move);
            $kingSquare = $move->piece->isKing() ? $move->toSquare : $king;
            if (null === $kingSquare || !$board->isAttacked($them, $kingSquare)) {
                $legalMoves[] = $move;
            }
            ($this->undoMove)();
        }

        return $legalMoves;
    }

    /**
     * @param array<Move> $moves
     */
    private static function addMove(string $turn, Board $board, array &$moves, int $from, int $to, int $flags): void
    {
        // if pawn promotion
        if ($board[$from] !== null && $board[$from]->isPawn() && (Board::rank($to) === Board::RANK_8 || Board::rank($to) === Board::RANK_1)) {
            foreach ([Piece::QUEEN, Piece::ROOK, Piece::BISHOP, Piece::KNIGHT] as $promotionPiece) {
                $moves[] = Move::buildMove($turn, $board, $from, $to, $flags, $promotionPiece);
            }
        } else {
            $moves[] = Move::buildMove($turn, $board, $from, $to, $flags);
        }
    }
}
