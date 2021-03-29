<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use Imagine\Image\AbstractImagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use PChess\Chess\Board;
use PChess\Chess\Chess;

final class ImagineOutput implements OutputInterface
{
    /**
     * @var AbstractImagine
     */
    private $imagine;

    /**
     * @var string
     */
    private $darkSquareColor;

    /**
     * @var string
     */
    private $liteSquareColor;

    /**
     * @var int
     */
    private $boardSize;

    /**
     * @var int
     */
    private $squareSize;

    /**
     * @var string
     */
    private $spritesPath;

    public function __construct(
        AbstractImagine $imagine,
        string $spritesPath,
        int $size = 400,
        string $darkSquareColor = '#8ca2ad',
        string $liteSquareColor = '#dee3e6'
    ) {
        $this->imagine = $imagine;
        $this->spritesPath = $spritesPath;
        $this->boardSize = $size;
        $this->squareSize = $this->boardSize / 8;
        if (!\is_int($this->squareSize)) {
            throw new \InvalidArgumentException(\sprintf('Size must be multiple of 8, %d given.', $this->boardSize));
        }
        $this->darkSquareColor = $darkSquareColor;
        $this->liteSquareColor = $liteSquareColor;
    }

    public function render(Chess $chess): string
    {
        $boardImage = $this->createBoard($chess->board);
        $pieceImages = $this->createPieceImages($chess->board);
        foreach ($chess->board as $i => $piece) {
            if (null === $piece) {
                continue;
            }
            $rank = Board::rank($i);
            $file = Board::file($i);
            if ($chess->board->isReversed()) {
                $file = 7 - $file;
                $rank = 7 - $rank;
            }
            $x = $file * $this->squareSize;
            $y = $rank * $this->squareSize;
            $boardImage->paste($pieceImages[(string) $piece], new Point($x, $y));
        }

        return $boardImage->get('png');
    }

    private function createBoard(Board $board): ImageInterface
    {
        $image = $this->imagine->create(new Box($this->boardSize, $this->boardSize));
        $palette = new RGB();
        foreach ($board as $i => $piece) {
            $rank = Board::rank($i);
            $file = Board::file($i);
            $hex = (($rank + $file) % 2 === 1) ? $this->darkSquareColor : $this->liteSquareColor;
            $x1 = $rank * $this->squareSize;
            $y1 = $file * $this->squareSize;
            $x2 = $x1 + $this->squareSize - 1;
            $y2 = $y1 + $this->squareSize - 1;
            $image->draw()->rectangle(new Point($x1, $y1), new Point($x2, $y2), $palette->color($hex), true);
        }

        return $image;
    }

    /**
     * @return array<string, ImageInterface>
     */
    private function createPieceImages(Board $board): array
    {
        $images = [];
        $size = new Box($this->squareSize, $this->squareSize);
        foreach ($board as $piece) {
            if (null === $piece) {
                continue;
            }
            $file = $this->spritesPath.$piece->getColor().$piece->getType().'.png';
            $images[(string) $piece] = $this->imagine->open($file)->resize($size);
        }

        return $images;
    }
}
