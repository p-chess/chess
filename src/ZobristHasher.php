<?php

declare(strict_types=1);

namespace PChess\Chess;

final class ZobristHasher
{
    private const TURN_ID = 1;
    private const EP_OFFSET = 100;
    private const CASTLING_OFFSET = 300;
    private const PIECE_OFFSET = 1000;

    /** @var array<int, string> */
    private static array $zobristCache = [];

    private string $hash = "\0\0\0\0\0\0\0\0";

    /**
     * @param array<string, int> $castling
     */
    public function rebuild(Board $board, string $turn, array $castling, ?int $epSquare): string
    {
        $this->hash = "\0\0\0\0\0\0\0\0";
        foreach (Board::SQUARES as $square) {
            $piece = $board[$square];
            if ($piece !== null) {
                $this->xorPiece($piece, $square);
            }
        }

        if ($turn === Piece::BLACK) {
            $this->hash ^= self::zobristKey(self::TURN_ID);
        }

        $this->xorCastlingDelta(Piece::WHITE, 0, $castling[Piece::WHITE]);
        $this->xorCastlingDelta(Piece::BLACK, 0, $castling[Piece::BLACK]);
        $this->xorEpSquare($epSquare);

        return $this->toHex();
    }

    public function applyMove(
        Move $move,
        string $us,
        string $them,
        ?int $previousEpSquare,
        ?int $currentEpSquare,
        int $previousUsCastling,
        int $currentUsCastling,
        int $previousThemCastling,
        int $currentThemCastling,
    ): string {
        $this->xorPiece($move->piece, $move->fromSquare);
        if (($move->flags & Move::BITS['CAPTURE']) > 0) {
            \assert(null !== $move->captured);
            $this->xorPieceByTypeAndColor($move->captured, $them, $move->toSquare);
        } elseif (($move->flags & Move::BITS['EP_CAPTURE']) > 0) {
            \assert(null !== $move->captured);
            $captureSquare = $move->toSquare + ($us === Piece::BLACK ? -16 : 16);
            $this->xorPieceByTypeAndColor($move->captured, $them, $captureSquare);
        }

        if (($move->flags & Move::BITS['PROMOTION']) > 0) {
            \assert(null !== $move->promotion);
            $this->xorPieceByTypeAndColor($move->promotion, $us, $move->toSquare);
        } else {
            $this->xorPiece($move->piece, $move->toSquare);
        }

        if (($move->flags & Move::BITS['KSIDE_CASTLE']) > 0) {
            $castlingTo = $move->toSquare - 1;
            $castlingFrom = $move->toSquare + 1;
            $this->xorPieceByTypeAndColor(Piece::ROOK, $us, $castlingFrom);
            $this->xorPieceByTypeAndColor(Piece::ROOK, $us, $castlingTo);
        } elseif (($move->flags & Move::BITS['QSIDE_CASTLE']) > 0) {
            $castlingTo = $move->toSquare + 1;
            $castlingFrom = $move->toSquare - 2;
            $this->xorPieceByTypeAndColor(Piece::ROOK, $us, $castlingFrom);
            $this->xorPieceByTypeAndColor(Piece::ROOK, $us, $castlingTo);
        }

        $this->xorEpSquare($previousEpSquare);
        $this->xorEpSquare($currentEpSquare);
        $this->xorCastlingDelta($us, $previousUsCastling, $currentUsCastling);
        $this->xorCastlingDelta($them, $previousThemCastling, $currentThemCastling);
        $this->hash ^= self::zobristKey(self::TURN_ID);

        return $this->toHex();
    }

    public function restore(string $hash): string
    {
        $binaryHash = \hex2bin($hash);
        if ($binaryHash === false || \strlen($binaryHash) !== 8) {
            throw new \RuntimeException('Invalid history position hash encoding.');
        }

        $this->hash = $binaryHash;

        return $this->toHex();
    }

    private function toHex(): string
    {
        return \bin2hex($this->hash);
    }

    private static function zobristKey(int $id): string
    {
        if (isset(self::$zobristCache[$id])) {
            return self::$zobristCache[$id];
        }

        self::$zobristCache[$id] = \substr(\hash('sha256', 'pchess-zobrist-'.$id, true), 0, 8);

        return self::$zobristCache[$id];
    }

    private static function pieceIndex(string $type, string $color): int
    {
        $colorOffset = $color === Piece::WHITE ? 0 : 6;

        $typeIndex = match ($type) {
            Piece::PAWN => 0,
            Piece::KNIGHT => 1,
            Piece::BISHOP => 2,
            Piece::ROOK => 3,
            Piece::QUEEN => 4,
            Piece::KING => 5,
            default => throw new \InvalidArgumentException('Invalid piece type: '.$type),
        };

        return $colorOffset + $typeIndex;
    }

    private static function castlingKeyId(string $color, int $flag): int
    {
        $colorOffset = $color === Piece::WHITE ? 0 : 2;
        $sideOffset = match ($flag) {
            Move::BITS['KSIDE_CASTLE'] => 0,
            Move::BITS['QSIDE_CASTLE'] => 1,
            default => throw new \InvalidArgumentException('Invalid castling flag: '.$flag),
        };

        return self::CASTLING_OFFSET + $colorOffset + $sideOffset;
    }

    private function pieceKeyId(string $type, string $color, int $square): int
    {
        return self::PIECE_OFFSET + (self::pieceIndex($type, $color) * 128) + $square;
    }

    private function xorPiece(Piece $piece, int $square): void
    {
        $this->hash ^= self::zobristKey($this->pieceKeyId($piece->getType(), $piece->getColor(), $square));
    }

    private function xorPieceByTypeAndColor(string $type, string $color, int $square): void
    {
        $this->hash ^= self::zobristKey($this->pieceKeyId($type, $color, $square));
    }

    private function xorEpSquare(?int $epSquare): void
    {
        if ($epSquare !== null) {
            $this->hash ^= self::zobristKey(self::EP_OFFSET + $epSquare);
        }
    }

    private function xorCastlingDelta(string $color, int $before, int $after): void
    {
        $changed = $before ^ $after;
        if (($changed & Move::BITS['KSIDE_CASTLE']) > 0) {
            $this->hash ^= self::zobristKey(self::castlingKeyId($color, Move::BITS['KSIDE_CASTLE']));
        }
        if (($changed & Move::BITS['QSIDE_CASTLE']) > 0) {
            $this->hash ^= self::zobristKey(self::castlingKeyId($color, Move::BITS['QSIDE_CASTLE']));
        }
    }
}
