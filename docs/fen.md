# Fen

Converts between [FEN](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation) strings and `Position` objects.
Both methods are static and need no `Chess` instance.

### `parse(string $fen, ?Board $board = null): Position`

Parses a FEN string. Throws `\InvalidArgumentException` if the string is invalid.
If `$board` is given, it is cleared and reused.
Only the first king of each color is placed: extra kings are dropped.

### `serialize(Position $position): string`

Gets the FEN string of a position.

```php
use PChess\Chess\Fen;

$position = Fen::parse('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1');
echo Fen::serialize($position);
```

Validation is delegated to `Validation::validateFen()`, which returns an error code (`0` means valid),
and `Validation::getError()`, which returns the message for a code.
