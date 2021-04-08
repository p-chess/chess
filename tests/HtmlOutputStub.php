<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PChess\Chess\Output\HtmlOutput;
use PChess\Chess\Output\Link;

final class HtmlOutputStub extends HtmlOutput
{
    public function generateLinks(Chess $chess, ?string $from = null, $identifier = null): array
    {
        $links = [];
        /** @var int $i */
        foreach ($chess->board as $i => $piece) {
            $links[$i] = new Link(null, null);
        }

        return $links;
    }
}
