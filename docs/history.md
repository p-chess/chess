# History

The `\Pchess\Chess\History` class contains instances of `\Pchess\Chess\Entry`.
Each entry is a value object representing a record of a move in [`\Pchess\Chess\Chess`](chess.md).

The `Entry` object is composed by the following properties:

|Property      | Type   | Description            |
|--------------|--------|------------------------|
|`$move`       | Move   | [Move object](move.md) |
|`$position`   | string | a hash of the board    |
|`$kings`      | array  | position of kings      |
|`$castling`   | array  | position of castlings  |
|`$epSquare`   | ?int   | en passant square      |
|`$halfMoves`  | int    | moves for 50-rule      |
|`$moveNumber` | int    | move number            |

