<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use PChess\Chess\Board;
use PChess\Chess\Chess;

/**
 * You need to extend this class and define your generateLinks() method.
 */
abstract class HtmlOutput implements OutputInterface
{
    /**
     * @param mixed $identifier
     */
    public function render(Chess $chess, ?string $from = null, $identifier = null): string
    {
        $links = $this->generateLinks($chess, $from, $identifier);
        $reversed = $chess->board->isReversed();
        $unsafe = false;
        $output = '<table id="'.$this->getBoardId().'">';
        /** @var int $i */
        foreach ($chess->board as $i => $piece) {
            if (($reversed && 7 === Board::file($i)) || (!$reversed && 0 === Board::file($i))) {
                $output .= '<tr><td class="'.$this->getFileClass().'">'.\substr('87654321', Board::rank($i), 1).'</td>';
            }
            if (null !== $piece || null !== $links[$i]->getClass()) {
                $aClass = null !== $piece ? ' class="'.$piece->getColor().$piece->getType().'"' : '';
                $unsafe = $unsafe || $links[$i]->unsafe;
                $output .= \sprintf('<td%s>%s</td>', $links[$i]->getClass(), self::renderControl($links[$i], $aClass));
            } else {
                $output .= \sprintf('<td%s></td>', $links[$i]->getClass());
            }
            if (($reversed && (0 !== (($i - 1) & 0x88))) || (!$reversed && (0 !== (($i + 1) & 0x88)))) {
                $output .= '</tr>'.\PHP_EOL;
            }
        }
        $output .= '<tr><td class="'.$this->getFileClass().'"></td>';
        $ranks = $reversed ? \range('h', 'a') : \range('a', 'h');
        foreach ($ranks as $rank) {
            $output .= '<td class="'.$this->getRankClass().'">'.$rank.'</td>';
        }
        $output .= '</tr></table>';

        return $unsafe ? $this->wrapInForm($output, $identifier) : $output;
    }

    /**
     * Generate an array of 64 Link objects to use in render() method.
     * Basically, you should cycle the board, assigning values to Link for pieces that can actually move.
     * There are two possible situations: 1) $from is null (move has to start); 2) $from is not null (move started).
     * In first case, you should assign a "start move" link to every piece that is allowed to move
     * (according to the result of $chess->moves() method).
     * In second case, you should assign a Link to cancel move start (on the same piece that started the move) to
     * the piece which SAN is identical to $from, and a Link to end move to every legal ending position
     * (according to the results of $chess->moves($from) method). You must pay attention to special case of
     * promotion (when piece is a pawn and is in rank 7).
     * Links that actually change the state of the game (i.e. links ending a move) should be flagged as unsafe,
     * so that they get rendered as submit buttons of a POST form.
     * A possible identifier can be passed, to make a distinction between different Chess objects.
     *
     * @param mixed $identifier
     *
     * @return array<int, Link>
     */
    abstract public function generateLinks(Chess $chess, ?string $from = null, $identifier = null): array;

    /**
     * Hidden fields (name => value) added to the form wrapping the board, typically a CSRF token.
     * They are used only when at least one of the links is unsafe.
     *
     * @param mixed $identifier
     *
     * @return array<string, string>
     */
    protected function getHiddenFields($identifier = null): array
    {
        return [];
    }

    protected function getBoardId(): string
    {
        return 'board';
    }

    protected function getFileClass(): string
    {
        return 'file';
    }

    protected function getRankClass(): string
    {
        return 'rank';
    }

    private static function renderControl(Link $link, string $class): string
    {
        if (!$link->unsafe) {
            return \sprintf('<a%s%s></a>', $class, $link->getUrl());
        }

        return \sprintf('<button type="submit"%s%s></button>', $class, $link->getFormAction());
    }

    /**
     * Every unsafe link carries its own "formaction", hence the form needs no action of its own.
     *
     * @param mixed $identifier
     */
    private function wrapInForm(string $board, $identifier): string
    {
        $fields = '';
        foreach ($this->getHiddenFields($identifier) as $name => $value) {
            $fields .= \sprintf(
                '<input type="hidden" name="%s" value="%s">',
                \htmlspecialchars($name, \ENT_QUOTES),
                \htmlspecialchars($value, \ENT_QUOTES),
            );
        }

        return '<form method="post">'.$fields.$board.'</form>';
    }
}
