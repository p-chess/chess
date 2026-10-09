# Board

The `\Pchess\Chess\Board` class is an array-like class, in which every element is a square of [`\Pchess\Chess\Chess`](chess.md).

The typical use of this class is calling `$board[$square]`, or cycling it:

```php
$board = new \PChess\Chess\Board();

// use it directly...
$piece = $board[$square];

// ... or cycle it
foreach ($board as $offset => $piece) {
    // $offset is an integer, $piece can be a Piece or null
}
```

The following methods can be used directly:

* `reverse()`: toggle sides (e.g. to see the board from the black point of view, and then from the white again)
* `isAttacked(string $color, int $square): bool`: check if a square is attacked by any piece of the given color
  (e.g. `$chess->board->isAttacked(Piece::WHITE, Board::SQUARES['e4'])`)
* `hasInsufficientMaterial(): bool`: check if material is insufficient to continue the game.
  Same as `Chess::insufficientMaterial()`

The following static methods can be used if you need to deal
with [SAN](https://en.wikipedia.org/wiki/Algebraic_notation_(chess)):

* `rank()`: get the rank of a square, as a number between 1 and 8
* `file()`: get the file of a square, as a number between 1 and 8
* `algebraic()`: get the SAN of a square, as a string (e.g. "e2").
   This should be the same as calling `Board::SQUARES[$square]`

Such methods accept an integer square (see `SQUARES` constant). 

Useful constants represent starting board (`Board::DEFAULT_POSITION`) and empty board (`Board::EMPTY`)
in [FEN](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation) notation.

