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

    protected SanEncoder $sanEncoder;
    protected MoveGenerator $moveGenerator;

    public function __construct(
        ?string $fen = Board::DEFAULT_POSITION,
        ?History $history = null,
        ?ZobristHasher $zobristHasher = null,
    ) {
        $this->position = Fen::parse($fen ?? Board::DEFAULT_POSITION);
        if (null !== $zobristHasher) {
            $this->position->useHasher($zobristHasher);
        }
        $this->board = $this->position->board;
        $this->history = $history ?? new History();
        $this->moveGenerator = new MoveGenerator();
        $this->sanEncoder = new SanEncoder($this->moveGenerator);
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
        $this->position->invalidateHash();

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
        $this->position->invalidateHash();
        if ($piece->isKing()) {
            $this->position->kings[$piece->getColor()] = $sq;
        }

        return true;
    }

    public function undo(): ?Move
    {
        $entry = $this->history->pop();
        if ($entry === null) {
            return null;
        }

        $move = $entry->move;
        if ($this->position->lastMove() === $move) {
            $this->position->undo();
        } else {
            // not played here (History given to the constructor), or history edited from outside
            $this->position->rewind($move, $entry->before);
        }
        $this->moveToSAN($move);

        return $move;
    }

    /**
     * @return array<int, Move>
     */
    protected function generateMoves(?int $square = null, bool $legal = true): array
    {
        return $this->moveGenerator->generate($this->position, $square, $legal);
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

        $entry = new Entry($moveObject, clone $this->position, $this->position->hash());
        $this->position->make($moveObject);
        $entry->hash = $this->position->hash();
        $this->history->add($entry);

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
        return $this->position->kingAttacked($color);
    }

    public function inCheck(): bool
    {
        return $this->position->inCheck();
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
        $move->san = $this->sanEncoder->encode($move, $this->position);
    }

    public function getHistory(): History
    {
        return $this->history;
    }
}
