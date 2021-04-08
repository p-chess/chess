<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Piece;

final class PgnOutput implements OutputInterface
{
    public function render(Chess $chess): string
    {
        $newline = $options['newline_char'] ?? PHP_EOL;
        $maxWidth = $options['max_width'] ?? 0;

        // process header
        $output = '';
        foreach ($chess->getHeader() as $header => $value) {
            $value = \addslashes($value);
            $output .= "[{$header} \"{$value}\"]".$newline;
        }

        if ($output !== '') {
            $output .= $newline;
        } // if header presented, add new empty line

        // process movements
        $currentWidth = 0;
        $i = 1;
        $moves = self::getMoves($chess);
        foreach ($moves as $move) {
            if ($i === 1 && $move->turn === Piece::BLACK) {
                $tmp = $i.'. ... ';
                ++$i;
            } else {
                $tmp = ($i % 2 === 1 ? \ceil($i / 2).'. ' : '');
            }
            $tmp .= $move->san.' ';

            $currentWidth += \strlen($tmp);
            if ($currentWidth > $maxWidth && $maxWidth > 0) {
                $tmp = $newline.$tmp;
                $currentWidth = 0;
            }

            $output .= $tmp;
            ++$i;
        }
        if ($i > 1) {
            $output = \substr($output, 0, -1);
        } // remove last space

        return $output;
    }

    /**
     * @return array<int, \PChess\Chess\Move>
     */
    private static function getMoves(Chess $chess): array
    {
        $moveHistory = [];
        $gameTmp = !empty($chess->getHeader()['SetUp']) ? new Chess($chess->getHeader()['FEN']) : new Chess();
        $moveTmp = [];

        foreach ($chess->getHistory() as $history) {
            $moveTmp['to'] = $history->move->to;
            $moveTmp['from'] = $history->move->from;
            if ($history->move->flags & Board::BITS['PROMOTION']) {
                $moveTmp['promotion'] = $history->move->promotion;
            }

            $turn = $gameTmp->turn;
            $moveObj = $gameTmp->move($moveTmp);

            if (null !== $moveObj) {
                $moveObj->turn = $turn;
                $moveHistory[] = $moveObj;
            }
            $moveTmp = [];
        }
        unset($gameTmp);

        //~ $move->flags |= Board::BITS['PROMOTION'];
        //~ $move->promotion = $promotion;
        return $moveHistory;
    }
}
