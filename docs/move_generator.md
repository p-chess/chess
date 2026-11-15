# MoveGenerator

Generates moves for the side to move.
`Chess` uses it internally for `moves()`, `move()` and game-end checks.

### `generate(Position $position, ?int $square = null, bool $legal = true): array<Move>`

Gets the moves of `$position`.
Pass a 0x88 `$square` to get only the moves of the piece on it.
Legality is checked by playing each move on `$position` with `make()` and taking it back with `undo()`,
so the position is left as it was.
With `$legal = false`, moves leaving the king in check are kept and the position is never touched.
Results are cached by position hash, square and `$legal`.
