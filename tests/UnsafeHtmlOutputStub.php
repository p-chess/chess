<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Output\HtmlOutput;
use PChess\Chess\Output\Link;

/**
 * Mimics a started move: e2 is the square the move started from, e4 is where it can end.
 */
final class UnsafeHtmlOutputStub extends HtmlOutput
{
    public function generateLinks(Chess $chess, ?string $from = null, $identifier = null): array
    {
        $links = [];
        /** @var int $i */
        foreach ($chess->board as $i => $piece) {
            $links[$i] = match (Board::algebraic($i)) {
                'e2' => new Link('current', '/cancel'),
                'e4' => new Link('target', '/move/e2/e4', true),
                default => new Link(null, null),
            };
        }

        return $links;
    }

    protected function getHiddenFields($identifier = null): array
    {
        return ['_token' => 'a&token'];
    }
}
