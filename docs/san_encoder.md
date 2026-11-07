# SanEncoder

Converts moves to [SAN](https://en.wikipedia.org/wiki/Algebraic_notation_(chess)) strings.
`Chess` uses it internally to fill `Move::$san`.

### `__construct(Closure $generateMoves, Closure $makeMove, Closure $undoMove, Closure $inCheck)`

The encoder holds no position. It gets what it needs through four callbacks:

* `$generateMoves(): array<Move>`: legal moves for the side to move
* `$makeMove(Move $move): void`: plays a move
* `$undoMove(): mixed`: takes back the last move
* `$inCheck(): bool`: whether the side to move is in check

### `encode(Move $move, string $hash): string`

Gets the SAN of a move, including the `+`/`#` suffix.
Results are cached by move and position hash.

### `disambiguator(Move $move, array $moves): string`

Static. Gets the file, rank or square needed to tell `$move` apart from other moves
of the same piece type to the same square. Returns an empty string if there is no ambiguity.
