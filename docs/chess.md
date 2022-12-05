# Chess

The `\Pchess\Chess\Chess` class is the main class of Chess library.
It represents a chess game.

## Main method

### `__construct(?string $fen, ?History $history)`

This is the main method. If you invoke it without arguments, you simply get an instance of the class.
If you decide to start a game from a particular position, you can pass it as first argument,
in [FEN](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation) notation.
Also, you can pass an [`\Pchess\Chess\History`](history.md) object (that is supposed to be consistent
with the first argument, even if no check is performed).
  
## Move-related

### `move($sanOrArray): ?Move`

The main method used to move a piece from a starting position to an ending position.
You can provide such information as a string in [SAN](https://en.wikipedia.org/wiki/Algebraic_notation_(chess))
notation (e.g. "Nxb7") or as an array with `from` and `to` keys. A possible `promotion` key can be used too.
If move is legal, a [`\Pchess\Chess\Move`](move.md) object is returned.

### `undo(): ?Move`
  
Cancels the last move (that is returned). If no move is present in history, method returns `null`;

### `moves(int $square): array`

Gets a list of allowed moves. If you pass a square, only move originating from that square are returned.
Otherwise, every possible move is returned.
The square must be passed as an integer, as coded in the [`\Pchess\Chess\Board::SQUARES`](board.md) constant.

## Board reading & writing
  
### `get(string $square): ?Piece`

Gets the [`\Pchess\Chess\Piece`](piece.md) placed in a square, if any. The square must be passed a string, as coded in
the [`\Pchess\Chess\Board::SQUARES`](board.md) constant.

### `put(Piece $piece, string $square): bool`

Alters the board, by putting a new [`\Pchess\Chess\Piece`](piece.md) in a square.
The square must be passed a string, as coded in the [`\Pchess\Chess\Board::SQUARES`](board.md) constant.
Please note that you cannot put two Kings of same color on the board. If you do so, method returns `false`.

## Game situation controls

### `inCheck(): bool`

Controls if a king is under attack.

### `inCheckmate(): bool`

Controls if there's a checkmate.

### `inStalemate(): bool`

Controls if there's a stalemate (i.e. king cannot do any legal move).

### `insufficientMaterial(): bool`

Controls if material is insufficient to continue the game (e.g. only kings are left).

### `inThreefoldRepetition(): bool`

Controls if same moves were repeated in last three turns.

### `halfMovesExceeded(): bool`

Controls if [fifty-move rule](https://en.wikipedia.org/wiki/Fifty-move_rule) is applicable.

### `inDraw(): bool`

Controls if there is a draw.

### `gameOver(): bool`

Controls if game is over.

## Utilities

### `fen(): string`

Gets the current position in [FEN](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation) notation.

### `getHistory(): History`

Gets [`\Pchess\Chess\Board\History`](history.md) object.

## Public properties

| Property | Type   | Description               |
|----------|--------|---------------------------|
| `$board` | Board  | [Board object](board.md)  |
| `$turn`  | string | current turn ("w" or "b") |
