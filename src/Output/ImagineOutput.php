<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

use Imagine\Gd\Font;
use Imagine\Image\AbstractImagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use PChess\Chess\Board;
use PChess\Chess\Chess;

final class ImagineOutput implements OutputInterface
{
    private AbstractImagine $imagine;

    private string $darkSquareColor;

    private string $liteSquareColor;

    private int $boardSize;

    private int $squareSize;

    private string $spritesPath;

    private ?int $coordSize = null;

    public function __construct(
        AbstractImagine $imagine,
        string $spritesPath,
        int $size = 400,
        bool $coords = false,
        string $darkSquareColor = '#8ca2ad',
        string $liteSquareColor = '#dee3e6'
    ) {
        $this->imagine = $imagine;
        $this->spritesPath = $spritesPath;
        $this->boardSize = $size;
        $squareSize = $this->boardSize / 8;
        if (!\is_int($squareSize)) {
            throw new \InvalidArgumentException(\sprintf('Size must be multiple of 8, %d given.', $this->boardSize));
        }
        $this->squareSize = $squareSize;
        $this->darkSquareColor = $darkSquareColor;
        $this->liteSquareColor = $liteSquareColor;
        $this->coordSize = $coords ? (int) \floor($this->squareSize / 6) : null;
    }

    public function render(Chess $chess): string
    {
        $boardImage = $this->createBoard($chess->board);
        if (null !== $this->coordSize) {
            $x = 0;
            $y = 0;
            $rankImages = $this->createRankImages();
            $fileImages = $this->createFileImages();
            if ($chess->board->isReversed()) {
                $rankImages = \array_reverse($rankImages);
                $fileImages = \array_reverse($fileImages);
            }
            foreach ($rankImages as $rankImage) {
                $boardImage->paste($rankImage, new Point($x, $y));
                $y += $this->squareSize;
            }
            $x = $this->coordSize;
            $y = $this->squareSize * 8;
            foreach ($fileImages as $fileImage) {
                $boardImage->paste($fileImage, new Point($x, $y));
                $x += $this->squareSize;
            }
        }
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
            $x = $file * $this->squareSize + ($this->coordSize ?: 0);
            $y = $rank * $this->squareSize;
            $boardImage->paste($pieceImages[(string) $piece], new Point($x, $y));
        }

        return $boardImage->get('png');
    }

    private function createBoard(Board $board): ImageInterface
    {
        $size = $this->coordSize ? $this->boardSize + $this->coordSize : $this->boardSize;
        $image = $this->imagine->create(new Box($size, $size));
        $palette = new RGB();
        foreach ($board as $i => $piece) {
            $rank = Board::rank($i);
            $file = Board::file($i);
            $hex = (($rank + $file) % 2 === 1) ? $this->darkSquareColor : $this->liteSquareColor;
            $x1 = $rank * $this->squareSize + ($this->coordSize ?: 0);
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

    /**
     * @return array<int, ImageInterface>
     */
    private function createRankImages(): array
    {
        $images = [];
        $ranks = \range(8, 1);
        $size = new Box($this->coordSize, $this->squareSize);
        $font = $this->getFont();
        $position = new Point($this->getCoordStartPoint(), $this->squareSize / 2);
        foreach ($ranks as $rank) {
            $images[$rank] = $this->imagine->create($size);
            $images[$rank]->draw()->text((string) $rank, $font, $position);
        }

        return $images;
    }

    /**
     * @return array<string, ImageInterface>
     */
    private function createFileImages(): array
    {
        $images = [];
        $files = \range('a', 'h');
        $size = new Box($this->squareSize, $this->coordSize);
        $font = $this->getFont();
        $position = new Point($this->squareSize / 2, $this->getCoordStartPoint());
        foreach ($files as $file) {
            $images[$file] = $this->imagine->create($size);
            $images[$file]->draw()->text($file, $font, $position);
        }

        return $images;
    }

    private function getFont(): Font
    {
        return new Font($this->spritesPath.'font.ttf', $this->getFontSize(), (new RGB())->color('#111111'));
    }

    private function getCoordStartPoint(): int
    {
        if ($this->squareSize > 50) {
            return 4;
        }
        if ($this->squareSize > 25) {
            return 2;
        }

        return 0;
    }

    private function getFontSize(): int
    {
        if ($this->squareSize >= 200) {
            return 11;
        }
        if ($this->squareSize > 100) {
            return 10;
        }
        if ($this->squareSize > 50) {
            return 8;
        }

        return 5;
    }
}
