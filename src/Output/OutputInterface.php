<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Chess;

interface OutputInterface
{
    public function render(Chess $chess): string;
}
