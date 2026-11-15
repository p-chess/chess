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

`$before` is a [Position](position.md) snapshot.
Note that `$before->board` is the live board, not a copy: undoing a move restores it from the `Move` itself.

Only moves played with `Chess::move()` are recorded. Moves played internally to check legality are not.
