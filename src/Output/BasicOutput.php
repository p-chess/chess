<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Board;

abstract class BasicOutput
{
    /** @var string */
    protected static $line = '   +---+---+---+---+---+---+---+---+'.PHP_EOL;

    protected static function getLines(bool $reversed, int $position): string
    {
        if (($reversed && 7 === Board::file($position)) || (!$reversed && 0 === Board::file($position))) {
            return ' '.\substr('87654321', Board::rank($position), 1).' |';
        }

        return '';
    }

    protected static function getEndLine(bool $reversed, int $position): string
    {
        if (($reversed && (0 !== (($position - 1) & 0x88))) || (!$reversed && (0 !== (($position + 1) & 0x88)))) {
            return ' '.PHP_EOL.self::$line;
        }

        return '';
    }

    protected static function getRanks(bool $reversed): string
    {
        if ($reversed) {
            return '     h   g   f   e   d   c   b   a';
        }

        return '     a   b   c   d   e   f   g   h';
    }
}
