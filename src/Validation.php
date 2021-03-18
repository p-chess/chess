<?php

declare(strict_types=1);

namespace PChess\Chess;

class Validation
{
    /**
     * @return array<string, mixed>
     */
    public static function validateFen(string $fen): array
    {
        $errors = [
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
            11 => 'Illegal en-passant square',
        ];

        $tokens = \explode(' ', $fen);

        // 1st criterion: 6 space-separated fields
        if (\count($tokens) !== 6) {
            return ['valid' => false, 'error_number' => 1, 'error' => $errors[1]];
        }

        // 2nd criterion: move number field is a integer value > 0
        if (!\ctype_digit($tokens[5]) || (int) $tokens[5] <= 0) {
            return ['valid' => false, 'error_number' => 2, 'error' => $errors[2]];
        }

        // 3rd criterion: half move counter is an integer >= 0
        if (!\ctype_digit($tokens[4]) || (int) $tokens[4] < 0) {
            return ['valid' => false, 'error_number' => 3, 'error' => $errors[3]];
        }

        // 4th criterion: 4th field is a valid e.p.-string
        if (!(\preg_match('/^(-|[a-h]{1}[3|6]{1})$/', $tokens[3]) === 1)) {
            return ['valid' => false, 'error_number' => 4, 'error' => $errors[4]];
        }

        // 5th criterion: 3th field is a valid castle-string
        if (!(\preg_match('/(^-$)|(^[K|Q|k|q]{1,}$)/', $tokens[2]) === 1)) {
            return ['valid' => false, 'error_number' => 5, 'error' => $errors[5]];
        }

        // 6th criterion: 2nd field is "w" (white) or "b" (black)
        if (!(\preg_match('/^(w|b)$/', $tokens[1]) === 1)) {
            return ['valid' => false, 'error_number' => 6, 'error' => $errors[6]];
        }

        // 7th criterion: 1st field contains 8 rows
        $rows = \explode('/', $tokens[0]);
        if (\count($rows) !== 8) {
            return ['valid' => false, 'error_number' => 7, 'error' => $errors[7]];
        }

        // 8-10th check
        foreach ($rows as $row) {
            $sumFields = 0;
            $previousWasNumber = false;
            for ($k = 0, $kMax = \strlen($row); $k < $kMax; ++$k) {
                if (\ctype_digit($row[$k])) {
                    // 8th criterion: every row is valid
                    if ($previousWasNumber) {
                        return ['valid' => false, 'error_number' => 8, 'error' => $errors[8]];
                    }
                    $sumFields += (int) $row[$k];
                    $previousWasNumber = true;
                } else {
                    // 9th criterion: check symbols of piece
                    if (\strpos(Piece::SYMBOLS, $row[$k]) === false) {
                        return ['valid' => false, 'error_number' => 9, 'error' => $errors[9]];
                    }
                    ++$sumFields;
                    $previousWasNumber = false;
                }
            }
            // 10th criterion: check sum piece + empty square must be 8
            if ($sumFields !== 8) {
                return ['valid' => false, 'error_number' => 10, 'error' => $errors[10]];
            }
        }

        // 11th criterion: en-passant if last is black's move, then its must be white turn
        if (\strlen($tokens[3]) > 1) {
            if (($tokens[3][1] == '3' && $tokens[1] === 'w') ||
                ($tokens[3][1] == '6' && $tokens[1] === 'b')) {
                return ['valid' => false, 'error_number' => 11, 'error' => $errors[11]];
            }
        }

        return ['valid' => true, 'error_number' => 0, 'error' => 'No errors.'];
    }

    /**
     *  @param array<string, mixed> $options
     *
     *  @return bool|array<string, mixed> if ($options['verbose'] == true), return compact('header', 'moves', 'game') or false
     */
    public static function validatePgn(string $pgn, array $options = [])
    {
        $parsedPgn = self::parsePgn($pgn);
        $verbose = !empty($options['verbose']) ? $options['verbose'] : false;

        $chess = new Chess();
        // validate move first before header for quick check invalid move
        foreach ($parsedPgn['moves'] as $k => $v) {
            if ($chess->move($v) === null) {
                return false;
            } // quick get out if move invalid
        }
        foreach ($parsedPgn['header'] as $k => $v) {
            $chess->header($k, $v);
        }
        $parsedPgn['game'] = $chess;

        return $verbose ? $parsedPgn : true;
    }

    /**
     * @return array<string, mixed>
     */
    public static function parsePgn(string $pgn): array
    {
        $header = [];
        $moves = [];

        // separate lines
        $lines = \str_replace("\r", "\n", $pgn);
        while (\strpos($lines, "\n\n") !== false) {
            $lines = \str_replace("\n\n", "\n", $lines);
        }
        $lines = \explode("\n", $lines);

        $parseHeader = true;
        foreach ($lines as $line) {

            // parse header
            if ($parseHeader) {
                \preg_match('/^\[(\S+) \"(.*)\"\]$/', $line, $matches);
                if (\count($matches) >= 3) {
                    $header[$matches[1]] = $matches[2];
                    continue;
                }
            }
            $parseHeader = false;

            // parse movements
            $movesTmp = \explode(' ', $line);
            foreach ($movesTmp as $moveTmp) {
                $moveTmp = \trim($moveTmp);
                $moveTmp = \preg_replace("/^(\d+)\.\s?/", '', $moveTmp);

                if (!\in_array($moveTmp, ['', '*', '1-0', '1/2-1/2', '0-1'], true)) {
                    $moves[] = $moveTmp;
                }
            }
        }

        return \compact('header', 'moves');
    }
}
