<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Board;
use PChess\Chess\Chess;

final class UnicodeOutput extends BasicOutput implements OutputInterface
{
    public function render(Chess $chess): string
    {
        $output = self::$line;
        for ($i = Board::SQUARES['a8']; $i <= Board::SQUARES['h1']; ++$i) {
            if (Board::file($i) === 0) {
                $output .= ' '.substr('87654321', Board::rank($i), 1).' |';
            }

            if ($chess->board[$i] === null) {
                $output .= '   |';
            } else {
                $output .= ' '.$chess->board[$i].' |';
            }

            if (($i + 1) & 0x88) {
                $output .= ' '.PHP_EOL;
                $output .= self::$line;
                ;
                $i += 8;
            }
        }
        $output .= self::$bottom;

        return $output;
    }
}
