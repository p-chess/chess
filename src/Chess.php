<?php

declare(strict_types=1);

namespace PChess\Chess;

class Chess
{
    /** @var Board&\ArrayAccess<int, ?Piece> */
    public $board;
    /** @var array<string, ?int> */
    protected $kings;
    /** @var string */
    public $turn;
    /** @var array<string, int> */
    protected $castling;
    /** @var int|null */
    protected $epSquare;
    /** @var int */
    protected $halfMoves;
    /** @var int */
    protected $moveNumber;
    /** @var array<int, History> */
    protected $history;
    /** @var array<string, string> */
    protected $header;
    /** @var array<string, array> */
    protected $generateMovesCache;
    /** @var string */
    protected $boardHash;
    /** @var array<string, string> */
    protected $sanMoveCache;

    public function __construct(?string $fen = null)
    {
        $this->board = new Board();
        $this->clear();

        if ((string) $fen !== '') {
            $this->load($fen);
        } else {
            $this->reset();
        }
    }

    public function clear(): void
    {
        $this->boardHash = \json_encode($this->board);
        $this->kings = [Piece::WHITE => null, Piece::BLACK => null];
        $this->turn = Piece::WHITE;
        $this->castling = [Piece::WHITE => 0, Piece::BLACK => 0];
        $this->epSquare = null;
        $this->halfMoves = 0;
        $this->moveNumber = 1;
        $this->history = [];
        $this->header = [];
        $this->generateMovesCache = [];
        $this->sanMoveCache = [];

        for ($i = 0; $i < 120; ++$i) {
            $this->board[$i] = null;
        }
    }

    protected function updateSetup(string $fen): void
    {
        if (\count($this->history) > 0) {
            return;
        }
        if ($fen !== Board::DEFAULT_POSITION) {
            $this->header['SetUp'] = '1';
            $this->header['FEN'] = $fen;
        } else {
            unset($this->header['SetUp'], $this->header['FEN']);
        }
    }

    /**
     * @param array<Move> $moves
     */
    private static function addMove(string $turn, Board $board, array &$moves, int $from, int $to, int $flags): void
    {
        // if pawn promotion
        if (
            $board[$from]->type === Piece::PAWN &&
            (Board::rank($to) === Board::RANK_8 || Board::rank($to) === Board::RANK_1)
        ) {
            $promotionPieces = [Piece::QUEEN, Piece::ROOK, Piece::BISHOP, Piece::KNIGHT];
            foreach ($promotionPieces as $promotionPiece) {
                $moves[] = Move::buildMove($turn, $board, $from, $to, $flags, $promotionPiece);
            }
        } else {
            $moves[] = Move::buildMove($turn, $board, $from, $to, $flags);
        }
    }

    /**
     * @return array<string, string>
     */
    public function header(?string $key = null, string $value = ''): array
    {
        if ($key !== null) {
            $this->header[$key] = $value;
        }

        return $this->header;
    }

    public function load(string $fen): bool
    {
        if (!Validation::validateFen($fen)['valid']) {
            return false;
        }
        $tokens = \explode(' ', $fen);
        $this->clear();

        // position
        $position = $tokens[0];
        $square = 0;
        for ($i = 0, $iMax = \strlen($position); $i < $iMax; ++$i) {
            $piece = $position[$i];
            if ($piece === '/') {
                $square += 8;
            } elseif (\ctype_digit($piece)) {
                $square += (int) $piece;
            } else {
                $color = (\ord($piece) < \ord('a')) ? Piece::WHITE : Piece::BLACK;
                $this->put(new Piece(\strtolower($piece), $color), Board::algebraic($square));
                ++$square;
            }
        }

        // turn
        $this->turn = $tokens[1];

        // castling options
        if (\strpos($tokens[2], 'K') !== false) {
            $this->castling[Piece::WHITE] |= Board::BITS['KSIDE_CASTLE'];
        }
        if (\strpos($tokens[2], 'Q') !== false) {
            $this->castling[Piece::WHITE] |= Board::BITS['QSIDE_CASTLE'];
        }
        if (\strpos($tokens[2], 'k') !== false) {
            $this->castling[Piece::BLACK] |= Board::BITS['KSIDE_CASTLE'];
        }
        if (\strpos($tokens[2], 'q') !== false) {
            $this->castling[Piece::BLACK] |= Board::BITS['QSIDE_CASTLE'];
        }

        // ep square
        $this->epSquare = ($tokens[3] === '-') ? null : Board::SQUARES[$tokens[3]];

        // half moves
        $this->halfMoves = (int) $tokens[4];

        // move number
        $this->moveNumber = (int) $tokens[5];

        $this->updateSetup($this->fen());

        return true;
    }

    public function reset(): bool
    {
        return $this->load(Board::DEFAULT_POSITION);
    }

    public function fen(): string
    {
        $empty = 0;
        $fen = '';
        for ($i = Board::SQUARES['a8']; $i <= Board::SQUARES['h1']; ++$i) {
            if (null === $piece = $this->board[$i]) {
                ++$empty;
            } else {
                if ($empty > 0) {
                    $fen .= $empty;
                    $empty = 0;
                }
                $fen .= $piece->toAscii();
            }

            if (($i + 1) & 0x88) {
                if ($empty > 0) {
                    $fen .= $empty;
                }
                if ($i !== Board::SQUARES['h1']) {
                    $fen .= '/';
                }
                $empty = 0;
                $i += 8;
            }
        }

        $cFlags = '';
        if ($this->castling[Piece::WHITE] & Board::BITS['KSIDE_CASTLE']) {
            $cFlags .= 'K';
        }
        if ($this->castling[Piece::WHITE] & Board::BITS['QSIDE_CASTLE']) {
            $cFlags .= 'Q';
        }
        if ($this->castling[Piece::BLACK] & Board::BITS['KSIDE_CASTLE']) {
            $cFlags .= 'k';
        }
        if ($this->castling[Piece::BLACK] & Board::BITS['QSIDE_CASTLE']) {
            $cFlags .= 'q';
        }
        if ($cFlags === '') {
            $cFlags = '-';
        }

        $epFlags = $this->epSquare === null ? '-' : Board::algebraic($this->epSquare);

        return \implode(' ', [$fen, $this->turn, $cFlags, $epFlags, $this->halfMoves, $this->moveNumber]);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<Move>
     */
    public function history(array $options = []): array
    {
        $moveHistory = [];
        $gameTmp = !empty($this->header['SetUp']) ? new self($this->header['FEN']) : new self();
        $moveTmp = [];
        $verbose = $options['verbose'] ?? false;

        foreach ($this->history as $history) {
            $moveTmp['to'] = $history->move->to;
            $moveTmp['from'] = $history->move->from;
            if ($history->move->flags & Board::BITS['PROMOTION']) {
                $moveTmp['promotion'] = $history->move->promotion;
            }

            $turn = $gameTmp->turn;
            $moveObj = $gameTmp->move($moveTmp);

            if (null !== $moveObj) {
                if ($verbose) {
                    $moveObj->turn = $turn;
                    $moveHistory[] = $moveObj;
                } else {
                    $moveHistory[] = $moveObj->san;
                }
            }
            $moveTmp = [];
        }

        //~ $move->flags |= Board::BITS['PROMOTION'];
        //~ $move->promotion = $promotion;
        return $moveHistory;
    }

    // this one from chess.js changed to return boolean (remove, true or false)
    public function remove(string $square): bool
    {
        // check for valid square
        if (!\array_key_exists($square, Board::SQUARES)) {
            return false;
        }

        $piece = $this->get($square);
        $this->board[Board::SQUARES[$square]] = null;

        if ($piece !== null && $piece->type === Piece::KING) {
            $this->kings[$piece->color] = null;
        }

        $this->updateSetup($this->fen());

        return true;
    }

    public function get(string $square): ?Piece
    {
        // check for valid square
        if (!\array_key_exists($square, Board::SQUARES)) {
            return null;
        }

        return $this->board[Board::SQUARES[$square]]; // shorcut?
    }

    public function put(Piece $piece, string $square): bool
    {
        // check for valid square
        if (!\array_key_exists($square, Board::SQUARES)) {
            return false;
        }

        $sq = Board::SQUARES[$square];

        // don't let the use place more than one king
        if ($piece->type === Piece::KING && !($this->kings[$piece->color] === null || $this->kings[$piece->color] === $sq)) {
            return false;
        }

        $this->board[$sq] = $piece;
        if ($piece->type === Piece::KING) {
            $this->kings[$piece->color] = $sq;
        }

        $this->updateSetup($this->fen());

        return true;
    }

    protected function makeMove(Move $move): void
    {
        $us = $this->turn;
        $them = self::swapColor($us);
        $historyKey = $this->recordMove($move);

        $this->board[$move->toSquare] = $this->board[$move->fromSquare];
        $this->board[$move->fromSquare] = null;

        // if flags:EP_CAPTURE (en passant), remove the captured pawn
        if ($move->flags & Board::BITS['EP_CAPTURE']) {
            $this->board[$move->toSquare + ($us === Piece::BLACK ? -16 : 16)] = null;
        }

        // if pawn promotion, replace with new piece
        if ($move->flags & Board::BITS['PROMOTION']) {
            $this->board[$move->toSquare] = new Piece($move->promotion, $us);
        }

        // if big pawn move, update the en passant square
        if ($move->flags & Board::BITS['BIG_PAWN']) {
            $this->epSquare = $move->toSquare + ($us === Piece::BLACK ? -16 : 16);
        } else {
            $this->epSquare = null;
        }

        // reset the 50 move counter if a pawn is moved or piece is captured
        if ($move->piece->type === Piece::PAWN) {
            $this->halfMoves = 0;
        } elseif ($move->flags & (Board::BITS['CAPTURE'] | Board::BITS['EP_CAPTURE'])) {
            $this->halfMoves = 0;
        } else {
            ++$this->halfMoves;
        }

        // if we moved the king
        if (null !== $this->board[$move->toSquare] && $this->board[$move->toSquare]->type === Piece::KING) {
            $this->kings[$us] = $move->toSquare;

            // if we castled, move the rook next to the king
            if ($move->flags & Board::BITS['KSIDE_CASTLE']) {
                $castlingTo = $move->toSquare - 1;
                $castlingFrom = $move->toSquare + 1;
                $this->board[$castlingTo] = $this->board[$castlingFrom];
                $this->board[$castlingFrom] = null;
            } elseif ($move->flags & Board::BITS['QSIDE_CASTLE']) {
                $castlingTo = $move->toSquare + 1;
                $castlingFrom = $move->toSquare - 2;
                $this->board[$castlingTo] = $this->board[$castlingFrom];
                $this->board[$castlingFrom] = null;
            }

            $this->castling[$us] = 0; // or maybe ''
        }

        // turn of castling of we move a rock
        if ($this->castling[$us] > 0) {
            for ($i = 0, $len = \count(Board::ROOKS[$us]); $i < $len; ++$i) {
                if (
                    $move->fromSquare === Board::ROOKS[$us][$i]['square'] &&
                    $this->castling[$us] & Board::ROOKS[$us][$i]['flag']
                ) {
                    $this->castling[$us] ^= Board::ROOKS[$us][$i]['flag'];
                    break;
                }
            }
        }

        // turn of castling of we capture a rock
        if ($this->castling[$them] > 0) {
            for ($i = 0, $len = \count(Board::ROOKS[$them]); $i < $len; ++$i) {
                if (
                    $move->toSquare === Board::ROOKS[$them][$i]['square'] &&
                    $this->castling[$them] & Board::ROOKS[$them][$i]['flag']
                ) {
                    $this->castling[$them] ^= Board::ROOKS[$them][$i]['flag'];
                    break;
                }
            }
        }

        if ($us === Piece::BLACK) {
            ++$this->moveNumber;
        }
        $this->turn = $them;

        $this->boardHash = \json_encode($this->board);
        $this->history[$historyKey]->position = $this->boardHash;
    }

    protected function recordMove(Move $move): int
    {
        $this->history[] = new History(
            $move,
            [Piece::WHITE => $this->kings[Piece::WHITE], Piece::BLACK => $this->kings[Piece::BLACK]],
            $this->turn,
            [Piece::WHITE => $this->castling[Piece::WHITE], Piece::BLACK => $this->castling[Piece::BLACK]],
            $this->epSquare,
            $this->halfMoves,
            $this->moveNumber
        );

        \end($this->history);

        return \key($this->history);
    }

    protected function undoMove(): ?Move
    {
        $old = \array_pop($this->history);
        if ($old === null) {
            return null;
        }

        $move = $old->move;
        $this->kings = $old->kings;
        $this->turn = $old->turn;
        $this->castling = $old->castling;
        $this->epSquare = $old->epSquare;
        $this->halfMoves = $old->halfMoves;
        $this->moveNumber = $old->moveNumber;

        $us = $this->turn;
        $them = self::swapColor($us);

        $this->board[$move->fromSquare] = $this->board[$move->toSquare];
        // to undo any promotions
        $oldFrom = $this->board[$move->fromSquare];
        if (null !== $oldFrom) {
            $oldFrom->type = $move->piece->type;
        }
        $this->board[$move->fromSquare] = $oldFrom;
        $this->board[$move->toSquare] = null;

        // if capture
        if ($move->flags & Board::BITS['CAPTURE']) {
            $this->board[$move->toSquare] = new Piece($move->captured, $them);
        } elseif ($move->flags & Board::BITS['EP_CAPTURE']) {
            $index = $move->toSquare + ($us === Piece::BLACK ? -16 : 16);
            $this->board[$index] = new Piece(Piece::PAWN, $them);
        }

        // if castling
        if ($move->flags & Board::BITS['KSIDE_CASTLE']) {
            $castlingTo = $move->toSquare + 1;
            $castlingFrom = $move->toSquare - 1;
            $this->board[$castlingTo] = $this->board[$castlingFrom];
            $this->board[$castlingFrom] = null;
        }
        if ($move->flags & Board::BITS['QSIDE_CASTLE']) {
            $castlingTo = $move->toSquare - 2;
            $castlingFrom = $move->toSquare + 1;
            $this->board[$castlingTo] = $this->board[$castlingFrom];
            $this->board[$castlingFrom] = null;
        }

        $this->boardHash = \json_encode($this->board);

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
     * @param array<string, mixed> $options
     *
     * @return array<int, Move>
     */
    protected function generateMoves(array $options = []): array
    {
        $cacheKey = $this->boardHash.\json_encode($options);

        // check cache first
        if (isset($this->generateMovesCache[$cacheKey])) {
            return $this->generateMovesCache[$cacheKey];
        }

        $moves = [];
        $us = $this->turn;
        $them = self::swapColor($us);
        $secondRank = [Piece::BLACK => Board::RANK_7, Piece::WHITE => Board::RANK_2];

        if (!empty($options['square'])) {
            $firstSquare = $lastSquare = $options['square'];
            $singleSquare = true;
        } else {
            $firstSquare = Board::SQUARES['a8'];
            $lastSquare = Board::SQUARES['h1'];
            $singleSquare = false;
        }

        // legal moves only?
        $legal = $options['legal'] ?? true;

        for ($i = $firstSquare; $i <= $lastSquare; ++$i) {
            if ($i & 0x88) {
                $i += 7;
                continue;
            } // check edge of board

            $piece = $this->board[$i];
            if ($piece === null || $piece->color !== $us) {
                continue;
            }

            if ($piece->type === Piece::PAWN) {
                // single square, non-capturing
                $square = $i + Piece::PAWN_OFFSETS[$us][0];
                if ($this->board[$square] === null) {
                    self::addMove($us, $this->board, $moves, $i, $square, Board::BITS['NORMAL']);

                    // double square
                    $square = $i + Piece::PAWN_OFFSETS[$us][1];
                    if ($secondRank[$us] === Board::rank($i) && $this->board[$square] === null) {
                        self::addMove($us, $this->board, $moves, $i, $square, Board::BITS['BIG_PAWN']);
                    }
                }

                // pawn captures
                for ($j = 2; $j < 4; ++$j) {
                    $square = $i + Piece::PAWN_OFFSETS[$us][$j];
                    if ($square & 0x88) {
                        continue;
                    }
                    if ($this->board[$square] !== null) {
                        if ($this->board[$square]->color === $them) {
                            self::addMove($us, $this->board, $moves, $i, $square, Board::BITS['CAPTURE']);
                        }
                    } elseif ($square === $this->epSquare) { // get epSquare from enemy
                        self::addMove($us, $this->board, $moves, $i, $this->epSquare, Board::BITS['EP_CAPTURE']);
                    }
                }
            } else {
                foreach (Piece::OFFSETS[$piece->type] as $jValue) {
                    $offset = $jValue;
                    $square = $i;

                    while (true) {
                        $square += $offset;
                        if ($square & 0x88) {
                            break;
                        }

                        if ($this->board[$square] === null) {
                            self::addMove($us, $this->board, $moves, $i, $square, Board::BITS['NORMAL']);
                        } else {
                            if ($this->board[$square]->color === $us) {
                                break;
                            }
                            self::addMove($us, $this->board, $moves, $i, $square, Board::BITS['CAPTURE']);
                            break;
                        }

                        if ($piece->type === Piece::KNIGHT || $piece->type === Piece::KING) {
                            break;
                        }
                    }
                }
            }
        }

        // castling
        // a) we're generating all moves
        // b) we're doing single square move generation on king's square
        if (!$singleSquare || $lastSquare === $this->kings[$us]) {
            if ($this->castling[$us] & Board::BITS['KSIDE_CASTLE']) {
                $castlingFrom = $this->kings[$us];
                $castlingTo = $castlingFrom + 2;

                if (
                    $this->board[$castlingFrom + 1] === null &&
                    $this->board[$castlingTo] === null &&
                    !$this->attacked($them, $this->kings[$us]) &&
                    !$this->attacked($them, $castlingFrom + 1) &&
                    !$this->attacked($them, $castlingTo)
                ) {
                    self::addMove($us, $this->board, $moves, $this->kings[$us], $castlingTo, Board::BITS['KSIDE_CASTLE']);
                }
            }

            if ($this->castling[$us] & Board::BITS['QSIDE_CASTLE']) {
                $castlingFrom = $this->kings[$us];
                $castlingTo = $castlingFrom - 2;

                if (
                    $this->board[$castlingFrom - 1] === null &&
                    $this->board[$castlingFrom - 2] === null && // $castlingTo
                    $this->board[$castlingFrom - 3] === null && // col "b", next to rock
                    !$this->attacked($them, $this->kings[$us]) &&
                    !$this->attacked($them, $castlingFrom - 1) &&
                    !$this->attacked($them, $castlingTo)
                ) {
                    self::addMove($us, $this->board, $moves, $this->kings[$us], $castlingTo, Board::BITS['QSIDE_CASTLE']);
                }
            }
        }

        // return all pseudo-legal moves (this includes moves that allow the king to be captured)
        if (!$legal) {
            $this->generateMovesCache[$cacheKey] = $moves;

            return $moves;
        }

        // filter out illegal moves
        $legalMoves = [];
        foreach ($moves as $i => $move) {
            $this->makeMove($move);
            if (!$this->kingAttacked($us)) {
                $legalMoves[] = $move;
            }
            $this->undoMove();
        }

        $this->generateMovesCache[$cacheKey] = $legalMoves;

        return $legalMoves;
    }

    /**
     * Move with SAN string (case-sensitive) or array.
     *
     * @param string|array<string, ?string> $sanOrArray E.g. "Nxb7" or ['from' => 'h7', 'to' => 'h8', 'promotion' => 'q']
     */
    public function move($sanOrArray): ?Move
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
            $sanOrArray['promotion'] = $sanOrArray['promotion'] ?? null;

            foreach ($moves as $move) {
                if (
                    ($move->promotion === null || $sanOrArray['promotion'] === $move->promotion) &&
                    $sanOrArray['from'] === $move->from &&
                    $sanOrArray['to'] === $move->to
                ) {
                    $moveObject = $move;
                    break;
                }
            }
        }

        if ($moveObject === null) {
            return null;
        }

        $this->moveToSAN($moveObject);
        $this->makeMove($moveObject);

        return $moveObject;
    }

    /**
     * The internal representation of a chess move is in 0x88 format, and
     * not meant to be human-readable.  The code below converts the 0x88
     * square coordinates to algebraic coordinates.  It also prunes an
     * unnecessary move keys resulting from a verbose call.
     *
     * @return array<int, Move>
     */
    public function moves(): array
    {
        $moves = [];
        foreach ($this->generateMoves() as $key => $move) {
            $this->moveToSAN($move);
            $moves[$key] = $move;
        }

        return $moves;
    }

    protected function attacked(string $color, int $square): bool
    {
        for ($i = Board::SQUARES['a8']; $i <= Board::SQUARES['h1']; ++$i) {
            if ($i & 0x88) {
                $i += 7;
                continue;
            }
            $piece = $this->board[$i];
            // check empty square and color
            if (null === $piece || $piece->color !== $color) {
                continue;
            }

            $difference = $i - $square;
            $index = $difference + 119;

            if (Board::ATTACKS[$index] & (1 << Piece::SHIFTS[$piece->type])) {
                if ($piece->type === Piece::PAWN) {
                    if ($difference > 0) {
                        if ($piece->color === Piece::WHITE) {
                            return true;
                        }
                    } elseif ($piece->color === Piece::BLACK) {
                        return true;
                    }
                    continue;
                }

                if ($piece->type === Piece::KNIGHT || $piece->type === Piece::KING) {
                    return true;
                }

                $offset = Board::RAYS[$index];
                $j = $i + $offset;
                $blocked = false;
                while ($j !== $square) {
                    if ($this->board[$j] !== null) {
                        $blocked = true;
                        break;
                    }
                    $j += $offset;
                }

                if (!$blocked) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function kingAttacked(string $color): bool
    {
        return $this->attacked(self::swapColor($color), $this->kings[$color]);
    }

    public function inCheck(): bool
    {
        return $this->kingAttacked($this->turn);
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
        $pieces = [
            Piece::PAWN => 0,
            Piece::KNIGHT => 0,
            Piece::BISHOP => 0,
            Piece::ROOK => 0,
            Piece::QUEEN => 0,
            Piece::KING => 0,
        ];
        $bishops = null;
        $numPieces = 0;
        $sqColor = 0;

        for ($i = Board::SQUARES['a8']; $i <= Board::SQUARES['h1']; ++$i) {
            $sqColor = ($sqColor + 1) % 2;
            if ($i & 0x88) {
                $i += 7;
                continue;
            }

            $piece = $this->board[$i];
            if ($piece !== null) {
                ++$pieces[$piece->type];
                if ($piece->type === Piece::BISHOP) {
                    $bishops[] = $sqColor;
                }
                ++$numPieces;
            }
        }

        // k vs k
        if ($numPieces === 2) {
            return true;
        }

        // k vs kn / k vs kb
        if ($numPieces === 3 && ($pieces[Piece::BISHOP] === 1 || $pieces[Piece::KNIGHT] === 1)) {
            return true;
        }

        // k(b){0,} vs k(b){0,}  , because maybe you are a programmer we talk in regex (preg) :-p
        if ($numPieces === $pieces[Piece::BISHOP] + 2) {
            $sum = 0;
            $len = \count($bishops);
            foreach ($bishops as $bishop) {
                $sum += $bishop;
            }
            if ($sum === 0 || $sum === $len) {
                return true;
            }
        }

        return false;
    }

    /* TODO: while this function is fine for casual use, a better
     * implementation would use a Zobrist key (instead of FEN). the
     * Zobrist key would be maintained in the make_move/undo_move functions,
     * avoiding the costly that we do below.
     */
    public function inThreefoldRepetition(): bool
    {
        $hash = [];
        foreach ($this->history as $history) {
            if (isset($hash[$history->position])) {
                ++$hash[$history->position];
            } else {
                $hash[$history->position] = 1;
            }

            if ($hash[$history->position] >= 3) {
                return true;
            }
        }

        return false;
    }

    public function halfMovesExceeded(): bool
    {
        return $this->halfMoves >= 100;
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

    // this function is used to uniquely identify ambiguous moves
    protected function getDisambiguator(Move $move): string
    {
        $moves = $this->generateMoves();

        $from = $move->fromSquare;
        $to = $move->toSquare;
        $pieceType = $move->piece->type;

        $ambiguities = 0;
        $sameRank = 0;
        $sameFile = 0;

        foreach ($moves as $aMove) {
            $ambiguityFrom = $aMove->fromSquare;
            $ambiguityTo = $aMove->toSquare;
            $ambiguityPieceType = $aMove->piece->type;

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

    // calculate SAN for Move
    protected function moveToSAN(Move $move): void
    {
        $cacheKey = \json_encode($move).$this->boardHash;
        if (isset($this->sanMoveCache[$cacheKey])) {
            $move->san = $this->sanMoveCache[$cacheKey];

            return;
        }

        $output = '';
        if ($move->flags & Board::BITS['KSIDE_CASTLE']) {
            $output = 'O-O';
        } elseif ($move->flags & Board::BITS['QSIDE_CASTLE']) {
            $output = 'O-O-O';
        } else {
            $disambiguator = $this->getDisambiguator($move);

            // pawn e2->e4 is "e4", knight g8->f6 is "Nf6"
            if ($move->piece->type !== Piece::PAWN) {
                $output .= \strtoupper($move->piece->type).$disambiguator;
            }

            // x on capture
            if ($move->flags & (Board::BITS['CAPTURE'] | Board::BITS['EP_CAPTURE'])) {
                // pawn e5->d6 is "exd6"
                if ($move->piece->type === Piece::PAWN) {
                    $output .= $move->from[0];
                }

                $output .= 'x';
            }

            $output .= $move->to;

            // promotion example: e8=Q
            if ($move->flags & Board::BITS['PROMOTION']) {
                $output .= '='.\strtoupper($move->promotion);
            }
        }

        // check / checkmate
        $this->makeMove($move);
        if ($this->inCheck()) {
            $output .= \count($this->generateMoves()) === 0 ? '#' : '+';
        }
        $this->undoMove();

        $this->sanMoveCache[$cacheKey] = $output;
        $move->san = $output;
    }

    /**
     * @return array<int, History>
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * @return array<string, string>
     */
    public function getHeader(): array
    {
        return $this->header;
    }
}
