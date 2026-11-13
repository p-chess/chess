# MoveGenerator

Generates moves for the side to move.
`Chess` uses it internally for `moves()`, `move()` and game-end checks.

### `__construct(Closure $makeMove, Closure $undoMove)`

Legality is checked by playing each move and taking it back.
The generator does not copy the position, so it needs two callbacks acting on the one passed to `generate()`:

* `$makeMove(Move $move): void`: plays a move
* `$undoMove(): mixed`: takes back the last move

### `generate(Position $position, string $hash, ?int $square = null, bool $legal = true): array<Move>`

Gets the moves of `$position`.
Pass a 0x88 `$square` to get only the moves of the piece on it.
With `$legal = false`, moves leaving the king in check are kept and the callbacks are never called.
Results are cached by position hash, square and `$legal`.
