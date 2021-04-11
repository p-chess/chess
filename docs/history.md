# History

The `\Pchess\Chess\History` class is a simple value object representing an entry of 
the history of moves in [`\Pchess\Chess\Chess`](chess.md).

The object is composed by the following properties:

|Property      | Type   | Description            |
|--------------|--------|------------------------|
|`$move`       | Move   | [Move object](move.md) |
|`$position`   | string | a hash of the board    |
|`$kings`      | array  | position of kings      |
|`$castling`   | array  | position of castlings  |
|`$epSquare`   | ?int   | en passant square      |
|`$halfMoves`  | int    | moves for 50-rule      |
|`$moveNumber` | int    | move number            |

