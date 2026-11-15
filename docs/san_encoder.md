# SanEncoder

Converts moves to [SAN](https://en.wikipedia.org/wiki/Algebraic_notation_(chess)) strings.
`Chess` uses it internally to fill `Move::$san`.

### `__construct(MoveGenerator $generator = new MoveGenerator())`

The generator gives the legal moves used for disambiguation and checkmate detection.

### `encode(Move $move, Position $position): string`

Gets the SAN of a move played from `$position`, including the `+`/`#` suffix.
The move is played and taken back on `$position` to find the suffix, so the position is left as it was.
Results are cached by move and position hash.

### `disambiguator(Move $move, array $moves): string`

Static. Gets the file, rank or square needed to tell `$move` apart from other moves
of the same piece type to the same square. Returns an empty string if there is no ambiguity.
