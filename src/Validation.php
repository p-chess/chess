<?php

declare(strict_types=1);

namespace PChess\Chess;

final class Validation
{
    private const CODES = [
        0 => 'No errors.',
        1 => 'FEN string must contain six space-delimited fields.',
        2 => '6th field (move number) must be a positive integer.',
        3 => '5th field (half move counter) must be a non-negative integer.',
        4 => '4th field (en-passant square) is invalid.',
        5 => '3rd field (castling availability) is invalid.',
        6 => '2nd field (side to move) is invalid.',
        7 => '1st field (piece positions) does not contain 8 \'/\'-delimited rows.',
        8 => '1st field (piece positions) is invalid [consecutive numbers].',
        9 => '1st field (piece positions) is invalid [invalid piece].',
        10 => '1st field (piece positions) is invalid [row too large].',
        11 => 'Illegal en-passant square.',
    ];

    public static function getError(int $code): string
    {
        return self::CODES[$code] ?? 'Invalid code.';
    }

    public static function validateFen(string $fen): int
    {
        $tokens = \explode(' ', $fen);

        // 1st criterion: 6 space-separated fields
        if (\count($tokens) !== 6) {
            return 1;
        }

        // 2nd criterion: move number field is an integer value > 0
        if (!\ctype_digit($tokens[5]) || (int) $tokens[5] <= 0) {
            return 2;
        }

        // 3rd criterion: half move counter is an integer >= 0
        if (!\ctype_digit($tokens[4]) || (int) $tokens[4] < 0) {
            return 3;
        }

        // 4th criterion: 4th field is a valid e.p. string
        if (!(\preg_match('/^(-|[a-h][3|6])$/', $tokens[3]) === 1)) {
            return 4;
        }

        // 5th criterion: 3rd field is a valid castle-string
        if (!(\preg_match('/(^-$)|(^[KQkq]+$)/', $tokens[2]) === 1)) {
            return 5;
        }

        // 6th criterion: 2nd field is "w" (white) or "b" (black)
        if ($tokens[1] !== Piece::WHITE && $tokens[1] !== Piece::BLACK) {
            return 6;
        }

        // 7th criterion: 1st field contains 8 rows
        $rows = \explode('/', $tokens[0]);
        if (\count($rows) !== 8) {
            return 7;
        }

        // 8-10th check
        foreach ($rows as $row) {
            $sumFields = 0;
            $previousWasNumber = false;
            for ($k = 0, $kMax = \strlen($row); $k < $kMax; ++$k) {
                if (\ctype_digit($row[$k])) {
                    // 8th criterion: every row is valid
                    if ($previousWasNumber) {
                        return 8;
                    }
                    $sumFields += (int) $row[$k];
                    $previousWasNumber = true;
                } else {
                    // 9th criterion: check symbols of piece
                    if (\strpos(Piece::SYMBOLS, $row[$k]) === false) {
                        return 9;
                    }
                    ++$sumFields;
                    $previousWasNumber = false;
                }
            }
            // 10th criterion: check sum piece + empty square must be 8
            if ($sumFields !== 8) {
                return 10;
            }
        }

        // 11th criterion: en-passant if last is black's move, then its must be white turn
        if (\strlen($tokens[3]) > 1) {
            if (($tokens[3][1] === '3' && $tokens[1] === Piece::WHITE) ||
                ($tokens[3][1] === '6' && $tokens[1] === Piece::BLACK)) {
                return 11;
            }
        }

        return 0;
    }
}
