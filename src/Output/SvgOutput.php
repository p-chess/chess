<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Board;
use PChess\Chess\Chess;

/**
 * The SVG template was adapted from https://github.com/michael-i-f-george/FEN2SVG
 * which is licensed under the GPL 3.0.
 */
final class SvgOutput implements OutputInterface
{
    private int $boardSize;

    private bool $coords;

    private string $darkSquareColor;

    private string $liteSquareColor;

    public function __construct(
        int $size = 626,
        bool $coords = true,
        string $darkSquareColor = 'LightSteelBlue',
        string $liteSquareColor = 'Gainsboro'
    ) {
        $this->boardSize = $size;
        $this->coords = $coords;
        $this->darkSquareColor = $darkSquareColor;
        $this->liteSquareColor = $liteSquareColor;
    }

    public function render(Chess $chess): string
    {
        $reversed = $chess->board->isReversed();
        $output = \file_get_contents(__DIR__.'/template.svg');
        $pieces = '';
        foreach ($chess->board as $i => $piece) {
            if (null === $piece) {
                continue;
            }
            if ($reversed) {
                $x = ((7 - Board::file($i)) * 72) + 49;
                $y = ((7 - Board::rank($i)) * 72) + 1;
            } else {
                $x = (Board::file($i) * 72) + 49;
                $y = (Board::rank($i) * 72) + 1;
            }
            $pieces .= \sprintf('<use xlink:href="#%s_%s" x="%d" y="%d"/>', $piece->getColor(), $piece->getType(), $x, $y).PHP_EOL;
        }

        $coords = '';
        if ($this->coords) {
            $file = $reversed ? '/coordinates_reversed.svg' : '/coordinates.svg';
            $coords = \file_get_contents(__DIR__.$file);
        }

        return \str_replace(
            ['{{ size }}', '{{ dark }}', '{{ light }}', '{{ coordinates }}', '{{ pieces }}'],
            [$this->boardSize, $this->darkSquareColor, $this->liteSquareColor, $coords, $pieces],
            $output
        );
    }
}
