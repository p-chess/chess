<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

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
            $value = addslashes($value);
            $output .= "[{$header} \"{$value}\"]".$newline;
        }

        if ($output !== '') {
            $output .= $newline;
        } // if header presented, add new empty line

        // process movements
        $currentWidth = 0;
        $i = 1;
        foreach ($chess->history(['verbose' => true]) as $history) {
            if ($i === 1 && $history->turn === Piece::BLACK) {
                $tmp = $i.'. ... ';
                ++$i;
            } else {
                $tmp = ($i % 2 === 1 ? ceil($i / 2).'. ' : '');
            }
            $tmp .= $history->san.' ';

            $currentWidth += strlen($tmp);
            if ($currentWidth > $maxWidth && $maxWidth > 0) {
                $tmp = $newline.$tmp;
                $currentWidth = 0;
            }

            $output .= $tmp;
            ++$i;
        }
        if ($i > 1) {
            $output = substr($output, 0, -1);
        } // remove last space

        return $output;
    }
}
