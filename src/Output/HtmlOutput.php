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
        $output = '<table id="'.$this->getBoardId().'">';
        /** @var int $i */
        foreach ($chess->board as $i => $piece) {
            if (($reversed && 7 === Board::file($i)) || (!$reversed && 0 === Board::file($i))) {
                $output .= '<tr><td class="'.$this->getFileClass().'">'.\substr('87654321', Board::rank($i), 1).'</td>';
            }
            if (null !== $piece || null !== $links[$i]->getClass()) {
                $aClass = $piece ? ' class="'.$piece->getColor().$piece->getType().'"' : '';
                $output .= \sprintf('<td%s><a%s%s></a></td>', $links[$i]->getClass(), $aClass, $links[$i]->getUrl());
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

        return $output;
    }

    /**
     * Generete an array of 64 Link objects to use in render() method.
     * Basically, you should cycle the board, assigning values to Link for pieces that can actually move.
     * There are two possible situations: 1) $from is null (move has to start); 2) $from is not null (move started).
     * In first case, you should assign a "start move" link to every piece that is allowed to move
     * (according to the result of $chess->moves() method).
     * In second case, you should assign a Link to cancel move start (on the same piece that started the move) to
     * the piece which SAN is identical to $from, and a Link to end move to every legal ending position
     * (according to the results of $chess->moves($from) method). You must pay attention to special case of
     * promotion (when piece is a pawn and is in rank 7).
     * A possible identifier can be passed, to make a distinction between different Chess objects.
     *
     * @param mixed $identifier
     *
     * @return array<int, Link>
     */
    abstract public function generateLinks(Chess $chess, ?string $from = null, $identifier = null): array;

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
}
