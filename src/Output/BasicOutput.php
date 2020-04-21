<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

abstract class BasicOutput
{
    /** @var string */
    protected static $line = '   +---+---+---+---+---+---+---+---+'.PHP_EOL;

    /** @var string */
    protected static $bottom = '     a   b   c   d   e   f   g   h'.PHP_EOL;
}
