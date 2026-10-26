# History

The `\Pchess\Chess\History` class contains instances of `\Pchess\Chess\Entry`.
Each entry is a value object representing a record of a move in [`\Pchess\Chess\Chess`](chess.md).

The `Entry` object is composed by the following properties:

| Property        | Type     | Description                                 |
|-----------------|----------|---------------------------------------------|
| `$move`         | Move     | [Move object](move.md)                      |
| `$before`       | Position | state before the move (see below)           |
| `$previousHash` | ?string  | a hash of the board before the move         |
| `$hash`         | ?string  | a hash of the board after the move          |

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

Note that `$before->board` is the live board, not a copy: undoing a move restores it from the `Move` itself.
