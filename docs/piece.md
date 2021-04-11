# Piece

The `\Pchess\Chess\Piece` class is a simple value object representing a piece in [`\Pchess\Chess\Chess`](chess.md).

The object is composed by a `$type` and a `$color` (both are a single-character string).

Type can be "k", "q", "r", "b", "n", or "p" (respectively King, Queen, Rook, Bishop, Knight, or Pawn).

Color can be "b" (black) or "w" (white).

String representation is piece type, in uppercase when white and in lowercase when black.
For example, "B" is a white Bishop, while "q" is a black Queen. 
