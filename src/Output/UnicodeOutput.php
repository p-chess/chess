<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Chess;

final class UnicodeOutput extends BasicOutput implements OutputInterface
{
    public function render(Chess $chess): string
    {
        $output = self::$line;
        $reversed = $chess->board->isReversed();
        foreach ($chess->board as $i => $piece) {
            $output .= self::getLines($reversed, $i);
            if (null === $piece) {
                $output .= '   |';
            } else {
                $output .= ' '.$piece->toUnicode().' |';
            }
            $output .= self::getEndLine($reversed, $i);
        }
        $output .= self::getRanks($reversed).PHP_EOL;

        return $output;
    }
}
