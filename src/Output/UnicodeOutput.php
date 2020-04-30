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
        foreach ($chess->board as $i => $piece) {
            if (Board::file($i) === 0) {
                $output .= ' '.\substr('87654321', Board::rank($i), 1).' |';
            }

            if (null === $piece) {
                $output .= '   |';
            } else {
                $output .= ' '.$piece->toUnicode().' |';
            }

            if (($i + 1) & 0x88) {
                $output .= ' '.PHP_EOL.self::$line;
            }
        }
        $output .= self::$bottom;

        return $output;
    }
}
