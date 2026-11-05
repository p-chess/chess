<?php

declare(strict_types=1);

namespace PChess\Chess;

/**
 * Converts between FEN strings and Position objects.
 */
final class Fen
{
    /**
     * @param ?Board $board if given, it is cleared and reused
     *
     * @throws \InvalidArgumentException
     */
    public static function parse(string $fen, ?Board $board = null): Position
    {
        $result = Validation::validateFen($fen);
        if ($result > 0) {
            throw new \InvalidArgumentException(\sprintf('Invalid fen: %s. Error: %s', $fen, Validation::getError($result)));
        }
        $tokens = \explode(' ', $fen);
        $position = Position::empty($board);

        // pieces
        $placement = $tokens[0];
        $square = 0;
        for ($i = 0, $iMax = \strlen($placement); $i < $iMax; ++$i) {
            $symbol = $placement[$i];
            if ($symbol === '/') {
                $square += 8;
            } elseif (\ctype_digit($symbol)) {
                $square += (int) $symbol;
            } else {
                $color = (\ord($symbol) < \ord('a')) ? Piece::WHITE : Piece::BLACK;
                $piece = new Piece(\strtolower($symbol), $color);
                // only one king per color: extra ones are dropped
                if ($piece->isKing()) {
                    if ($position->kings[$color] === null) {
                        $position->board[$square] = $piece;
                        $position->kings[$color] = $square;
                    }
                } else {
                    $position->board[$square] = $piece;
                }
                ++$square;
            }
        }

        // turn
        $position->turn = $tokens[1];

        // castling options
        if (\str_contains($tokens[2], 'K')) {
            $position->castling[Piece::WHITE] |= Move::BITS['KSIDE_CASTLE'];
        }
        if (\str_contains($tokens[2], 'Q')) {
            $position->castling[Piece::WHITE] |= Move::BITS['QSIDE_CASTLE'];
        }
        if (\str_contains($tokens[2], 'k')) {
            $position->castling[Piece::BLACK] |= Move::BITS['KSIDE_CASTLE'];
        }
        if (\str_contains($tokens[2], 'q')) {
            $position->castling[Piece::BLACK] |= Move::BITS['QSIDE_CASTLE'];
        }

        // ep square
        $position->epSquare = ($tokens[3] === '-') ? null : Board::SQUARES[$tokens[3]];

        // half moves
        $position->halfMoves = (int) $tokens[4];

        // move number
        $position->moveNumber = (int) $tokens[5];

        return $position;
    }

    public static function serialize(Position $position): string
    {
        $empty = 0;
        $fen = '';
        for ($i = Board::SQUARES['a8']; $i <= Board::SQUARES['h1']; ++$i) {
            if (null === $piece = $position->board[$i]) {
                ++$empty;
            } else {
                if ($empty > 0) {
                    $fen .= $empty;
                    $empty = 0;
                }
                $fen .= $piece->toAscii();
            }

            if ((($i + 1) & 0x88) > 0) {
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
        if (($position->castling[Piece::WHITE] & Move::BITS['KSIDE_CASTLE']) > 0) {
            $cFlags .= 'K';
        }
        if (($position->castling[Piece::WHITE] & Move::BITS['QSIDE_CASTLE']) > 0) {
            $cFlags .= 'Q';
        }
        if (($position->castling[Piece::BLACK] & Move::BITS['KSIDE_CASTLE']) > 0) {
            $cFlags .= 'k';
        }
        if (($position->castling[Piece::BLACK] & Move::BITS['QSIDE_CASTLE']) > 0) {
            $cFlags .= 'q';
        }
        if ($cFlags === '') {
            $cFlags = '-';
        }

        $epFlags = $position->epSquare === null ? '-' : Board::algebraic($position->epSquare);

        return \implode(' ', [$fen, $position->turn, $cFlags, $epFlags, $position->halfMoves, $position->moveNumber]);
    }
}
