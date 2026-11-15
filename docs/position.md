# Position

`\Pchess\Chess\Position` holds the state of the game:

| Property      | Type   | Description           |
|---------------|--------|-----------------------|
| `$board`      | Board  | [the board](board.md) |
| `$turn`       | string | side to move          |
| `$kings`      | array  | position of kings     |
| `$castling`   | array  | castling rights       |
| `$epSquare`   | ?int   | en passant square     |
| `$halfMoves`  | int    | moves for 50-rule     |
| `$moveNumber` | int    | move number           |

It is mutable on purpose: move generation plays and takes back moves in place.
Build one with [`Fen::parse()`](fen.md) or `Position::empty()`.

### `make(Move $move): void`

Plays a move, without checking it is legal. Keeps the hash up to date.

### `undo(): ?Move`

Takes back the last move played with `make()`. Returns `null` if there is none.

### `lastMove(): ?Move`

The move `undo()` would take back, or `null` if there is none.

### `rewind(Move $move, Position $before): void`

Takes back a move not played with `make()`, restoring the state from the `$before` snapshot.
Drops any pending undo records, since they no longer match the board.

### `hash(): string`

Zobrist hash of the position. Identical positions get the same hash.
After changing properties or the board directly, call `invalidateHash()` so it is rebuilt.

### `inCheck(): bool`

Checks if the side to move is in check.

### `kingAttacked(string $color): bool`

Checks if the king of `$color` is attacked.

A clone is a snapshot: it shares the board and has nothing to undo.
