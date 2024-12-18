# Move

The `\Pchess\Chess\Move` class is a simple value object representing a move in [`\Pchess\Chess\Chess`](chess.md)

The following public properties are available:

| Property      | Type    | Description              |
|---------------|---------|--------------------------|
| `$turn`       | string  | 'w' or 'b'               |
| `$flags`      | int     | move type bits           |
| `$piece`      | Piece   | [Piece object](piece.md) |
| `$from`       | string  | from square              |
| `$to`         | string  | to square                |
| `$fromSquare` | int     | from square              |
| `$toSquare`   | int     | to square                |
| `$captured`   | ?string | captured piece           |
| `$promotion`  | ?string | promotion                |
| `$san`        | ?string | notation                 |

Squares are coded like in keys and/or values of `\Pchess\Chess\Board::SQUARES`
Notation is [SAN](https://en.wikipedia.org/wiki/Algebraic_notation_(chess))

`Move` can be built directly with its constructor, passing all arguments in respective order.
Or it can be built using the static method `Move::buildMove()`:

```php
public static function buildMove(
    string $turn,
    Board $board,
    int $from,
    int $to,
    int $flags,
    ?string $promotion = null
): self
```

The `$san` property is set in the `move()` method of [`\Pchess\Chess\Chess`](chess.md) class.

The information about move type, stored in `$flags` properties as bits, is:

| Bit | Name         | Description         |
|-----|--------------|---------------------|
| 1   | NORMAL       | Normal move         |
| 2   | CAPTURE      | Capture             |
| 4   | BIG_PAWN     | Pawn 2-squares move |
| 8   | EP_CAPTURE   | En passant          |
| 16  | PROMOTION    | Promotion           |
| 32  | KSIDE_CASTLE | Short castling      |
| 64  | QSIDE_CASTLE | Long castling       |

