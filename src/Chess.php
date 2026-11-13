<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * @property-read string $turn
 */
class Chess
{
    /** @var Board&\ArrayAccess<int, ?Piece> */
    public Board $board;
    protected Position $position;

    protected History $history;

    protected string $boardHash = '';
    protected ZobristHasher $zobristHasher;
    protected SanEncoder $sanEncoder;
    protected MoveGenerator $moveGenerator;

    public function __construct(
        ?string $fen = Board::DEFAULT_POSITION,
        ?History $history = null,
        ?ZobristHasher $zobristHasher = null,
    ) {
        $this->zobristHasher = $zobristHasher ?? new ZobristHasher();
        $this->position = Fen::parse($fen ?? Board::DEFAULT_POSITION);
        $this->board = $this->position->board;
        $this->refreshBoardHash();
        $this->history = $history ?? new History();
        $this->moveGenerator = new MoveGenerator($this->makeMove(...), $this->undoMove(...));
        $this->sanEncoder = new SanEncoder(
            $this->generateMoves(...),
            $this->makeMove(...),
            $this->undoMove(...),
            $this->inCheck(...),
        );
    }

    public function __get(string $name): mixed
    {
        if ($name === 'turn') {
            return $this->position->turn;
        }

        throw new \LogicException(\sprintf('Undefined property: %s::$%s', self::class, $name));
    }

    public function __isset(string $name): bool
    {
        return $name === 'turn';
    }

    public function fen(): string
    {
        return Fen::serialize($this->position);
    }

    public function remove(string $square): bool
    {
        // check for valid square
        if (!\array_key_exists($square, Board::SQUARES)) {
            return false;
        }

        $piece = $this->get($square);
        $this->board[Board::SQUARES[$square]] = null;

        if ($piece !== null && $piece->isKing()) {
            $this->position->kings[$piece->getColor()] = null;
        }

        return true;
    }

    public function get(string $square): ?Piece
    {
        // check for valid square
        if (!\array_key_exists($square, Board::SQUARES)) {
            return null;
        }

        return $this->board[Board::SQUARES[$square]]; // shortcut?
    }

    public function put(Piece $piece, string $square): bool
    {
        // check for valid square
        if (!\array_key_exists($square, Board::SQUARES)) {
            return false;
        }

        $sq = Board::SQUARES[$square];

        // don't let the use place more than one king
        if ($piece->isKing() && !($this->position->kings[$piece->getColor()] === null || $this->position->kings[$piece->getColor()] === $sq)) {
            return false;
        }

        $this->board[$sq] = $piece;
        if ($piece->isKing()) {
            $this->position->kings[$piece->getColor()] = $sq;
        }

        return true;
    }

    protected function makeMove(Move $move): void
    {
        $us = $this->position->turn;
        $them = self::swapColor($us);
        $historyKey = $this->recordMove($move);
        $previousEpSquare = $this->position->epSquare;
        $previousUsCastling = $this->position->castling[$us];
        $previousThemCastling = $this->position->castling[$them];
        $hashablePreviousEpSquare = $this->hashableEpSquare($previousEpSquare, $us);

        $this->board[$move->toSquare] = $this->board[$move->fromSquare];
        $this->board[$move->fromSquare] = null;

        // if flags:EP_CAPTURE (en passant), remove the captured pawn
        if (($move->flags & Move::BITS['EP_CAPTURE']) > 0) {
            $this->board[$move->toSquare + ($us === Piece::BLACK ? -16 : 16)] = null;
        }

        // if pawn promotion, replace with new piece
        if (($move->flags & Move::BITS['PROMOTION']) > 0) {
            \assert(null !== $move->promotion);
            $this->board[$move->toSquare] = new Piece($move->promotion, $us);
        }

        // if big pawn move, update the en passant square
        if (($move->flags & Move::BITS['BIG_PAWN']) > 0) {
            $this->position->epSquare = $move->toSquare + ($us === Piece::BLACK ? -16 : 16);
        } else {
            $this->position->epSquare = null;
        }

        // reset the 50 move counter if a pawn is moved or piece is captured
        if ($move->piece->isPawn()) {
            $this->position->halfMoves = 0;
        } elseif (($move->flags & (Move::BITS['CAPTURE'] | Move::BITS['EP_CAPTURE'])) > 0) {
            $this->position->halfMoves = 0;
        } else {
            ++$this->position->halfMoves;
        }

        // if we moved the king
        if (null !== $this->board[$move->toSquare] && $this->board[$move->toSquare]->isKing()) {
            $this->position->kings[$us] = $move->toSquare;

            // if we castled, move the rook next to the king
            if (($move->flags & Move::BITS['KSIDE_CASTLE']) > 0) {
                $castlingTo = $move->toSquare - 1;
                $castlingFrom = $move->toSquare + 1;
                $this->board[$castlingTo] = $this->board[$castlingFrom];
                $this->board[$castlingFrom] = null;
            } elseif (($move->flags & Move::BITS['QSIDE_CASTLE']) > 0) {
                $castlingTo = $move->toSquare + 1;
                $castlingFrom = $move->toSquare - 2;
                $this->board[$castlingTo] = $this->board[$castlingFrom];
                $this->board[$castlingFrom] = null;
            }

            $this->position->castling[$us] = 0; // or maybe ''
        }

        // turn of castling of we move a rock
        if ($this->position->castling[$us] > 0) {
            for ($i = 0, $len = \count(Board::ROOKS[$us]); $i < $len; ++$i) {
                if (
                    ($move->fromSquare === Board::ROOKS[$us][$i]['square']) > 0 &&
                    ($this->position->castling[$us] & Board::ROOKS[$us][$i]['flag']) > 0
                ) {
                    $this->position->castling[$us] ^= Board::ROOKS[$us][$i]['flag'];
                    break;
                }
            }
        }

        // turn of castling of we capture a rock
        if ($this->position->castling[$them] > 0) {
            for ($i = 0, $len = \count(Board::ROOKS[$them]); $i < $len; ++$i) {
                if (
                    $move->toSquare === Board::ROOKS[$them][$i]['square'] &&
                    ($this->position->castling[$them] & Board::ROOKS[$them][$i]['flag']) > 0
                ) {
                    $this->position->castling[$them] ^= Board::ROOKS[$them][$i]['flag'];
                    break;
                }
            }
        }

        if ($us === Piece::BLACK) {
            ++$this->position->moveNumber;
        }
        $this->position->turn = $them;

        $hashableCurrentEpSquare = $this->hashableEpSquare($this->position->epSquare, $this->position->turn);

        $this->boardHash = $this->zobristHasher->applyMove(
            $move,
            $us,
            $them,
            $hashablePreviousEpSquare,
            $hashableCurrentEpSquare,
            $previousUsCastling,
            $this->position->castling[$us],
            $previousThemCastling,
            $this->position->castling[$them],
        );
        $this->history->get($historyKey)->hash = $this->boardHash;
    }

    protected function recordMove(Move $move): int
    {
        $this->history->add(new Entry($move, clone $this->position, $this->boardHash));

        return \count($this->history->getEntries()) - 1;
    }

    protected function undoMove(): ?Move
    {
        $old = $this->history->pop();
        if ($old === null) {
            return null;
        }

        $move = $old->move;
        $this->position = clone $old->before;
        // a History given to the constructor may carry a different Board
        $this->position->board = $this->board;

        $us = $this->position->turn;
        $them = self::swapColor($us);

        $this->board[$move->fromSquare] = $this->board[$move->toSquare];
        if (null !== $oldFrom = $this->board[$move->fromSquare]) {
            // to undo any promotions
            $oldFrom = new Piece($move->piece->getType(), $move->piece->getColor());
        }
        $this->board[$move->fromSquare] = $oldFrom;
        $this->board[$move->toSquare] = null;

        // if capture
        if (($move->flags & Move::BITS['CAPTURE']) > 0) {
            \assert(null !== $move->captured);
            $this->board[$move->toSquare] = new Piece($move->captured, $them);
        } elseif (($move->flags & Move::BITS['EP_CAPTURE']) > 0) {
            $index = $move->toSquare + ($us === Piece::BLACK ? -16 : 16);
            $this->board[$index] = new Piece(Piece::PAWN, $them);
        }

        // if castling
        if (($move->flags & Move::BITS['KSIDE_CASTLE']) > 0) {
            $castlingTo = $move->toSquare + 1;
            $castlingFrom = $move->toSquare - 1;
            $this->board[$castlingTo] = $this->board[$castlingFrom];
            $this->board[$castlingFrom] = null;
        }
        if (($move->flags & Move::BITS['QSIDE_CASTLE']) > 0) {
            $castlingTo = $move->toSquare - 2;
            $castlingFrom = $move->toSquare + 1;
            $this->board[$castlingTo] = $this->board[$castlingFrom];
            $this->board[$castlingFrom] = null;
        }

        if ($old->previousHash === null) {
            $this->refreshBoardHash();
        } else {
            $this->boardHash = $this->zobristHasher->restore($old->previousHash);
        }

        return $move;
    }

    public function undo(): ?Move
    {
        $move = $this->undoMove();
        if (null !== $move) {
            $this->moveToSAN($move);
        }

        return $move;
    }

    /**
     * @return array<int, Move>
     */
    protected function generateMoves(?int $square = null, bool $legal = true): array
    {
        return $this->moveGenerator->generate($this->position, $this->boardHash, $square, $legal);
    }

    /**
     * Move with SAN string (case-sensitive) or array.
     *
     * @param string|array<string, ?string> $sanOrArray E.g. "Nxb7" or ['from' => 'h7', 'to' => 'h8', 'promotion' => 'q']
     */
    public function move(string|array $sanOrArray): ?Move
    {
        $moveObject = null;
        $moves = $this->generateMoves();

        if (\is_string($sanOrArray)) {
            foreach ($moves as $move) {
                $this->moveToSAN($move);
                if ($move->san === $sanOrArray) {
                    $moveObject = $move;
                    break;
                }
            }
        } elseif (\is_array($sanOrArray)) {
            $sanOrArray['promotion'] ??= null;

            foreach ($moves as $move) {
                if (
                    ($move->promotion === null || $sanOrArray['promotion'] === $move->promotion) &&
                    $sanOrArray['from'] === $move->from &&
                    $sanOrArray['to'] === $move->to
                ) {
                    $moveObject = $move;
                    $this->moveToSAN($moveObject);
                    break;
                }
            }
        }

        if ($moveObject === null) {
            return null;
        }

        $this->makeMove($moveObject);

        return $moveObject;
    }

    /**
     * The internal representation of a chess move is in 0x88 format, and
     * not meant to be human-readable. The code below converts the 0x88
     * square coordinates to algebraic coordinates. It also prunes an
     * unnecessary move keys resulting from a verbose call.
     *
     * @return array<int, Move>
     */
    public function moves(?int $square = null): array
    {
        $moves = [];
        foreach ($this->generateMoves($square) as $key => $move) {
            $this->moveToSAN($move);
            $moves[$key] = $move;
        }

        return $moves;
    }

    protected function attacked(string $color, int $square): bool
    {
        return $this->board->isAttacked($color, $square);
    }

    protected function kingAttacked(string $color): bool
    {
        return null !== $this->position->kings[$color] && $this->board->isAttacked(self::swapColor($color), $this->position->kings[$color]);
    }

    public function inCheck(): bool
    {
        return $this->kingAttacked($this->position->turn);
    }

    public function inCheckmate(): bool
    {
        return $this->inCheck() && \count($this->generateMoves()) === 0;
    }

    public function inStalemate(): bool
    {
        return !$this->inCheck() && \count($this->generateMoves()) === 0;
    }

    public function insufficientMaterial(): bool
    {
        return $this->board->hasInsufficientMaterial();
    }

    /* TODO: while this function is fine for casual use, a better
     * implementation would use a Zobrist key (instead of FEN). The
     * Zobrist key would be maintained in the make_move/undo_move functions,
     * avoiding the costly that we do below.
     */
    public function inThreefoldRepetition(): bool
    {
        $hash = [];
        foreach ($this->history->getEntries() as $entry) {
            if ($entry->hash === null) {
                continue;
            }

            if (isset($hash[$entry->hash])) {
                ++$hash[$entry->hash];
            } else {
                $hash[$entry->hash] = 1;
            }

            if ($hash[$entry->hash] >= 3) {
                return true;
            }
        }

        return false;
    }

    public function halfMovesExceeded(): bool
    {
        return $this->position->halfMoves >= 100;
    }

    public function inDraw(): bool
    {
        return
            $this->halfMovesExceeded() ||
            //~ $this->inCheckmate() ||
            $this->inStalemate() ||
            $this->insufficientMaterial() ||
            $this->inThreefoldRepetition()
        ;
    }

    public function gameOver(): bool
    {
        return $this->inDraw() || $this->inCheckmate();
    }

    protected static function swapColor(string $color): string
    {
        return $color === Piece::WHITE ? Piece::BLACK : Piece::WHITE;
    }

    protected function moveToSAN(Move $move): void
    {
        $move->san = $this->sanEncoder->encode($move, $this->boardHash);
    }

    public function getHistory(): History
    {
        return $this->history;
    }

    private function refreshBoardHash(): void
    {
        $this->boardHash = $this->zobristHasher->rebuild(
            $this->board,
            $this->position->turn,
            $this->position->castling,
            $this->hashableEpSquare($this->position->epSquare, $this->position->turn),
        );
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
}
