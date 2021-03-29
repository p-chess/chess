# Chess

Chess is a PHP chess library that is used for chess move
generation/validation, piece placement/movement, and check/checkmate/stalemate
detection - basically everything but the AI. 

NOTE: this started as a port of [chess.js](https://github.com/jhlywa/chess.js) for php, froked from [ryanhs/chess.php](https://github.com/ryanhs/chess.php)

[![Latest Stable Version](https://poser.pugx.org/p-chess/chess/v/stable)](https://packagist.org/p-chess/chess)
[![buddy branch](https://app.buddy.works/akondas/chess/repository/branch/master/badge.svg?token=bfd952ec0cee0cb4db84dbd50ded487354ee6c9f37a7034f7c46425fed70dea7 "buddy branch")](https://app.buddy.works/akondas/chess/repository/branch/master)
[![MIT License](https://poser.pugx.org/p-chess/chess/license)](https://packagist.org/packages/p-chess/chess)  

## Installation

use composer with `composer require p-chess/chess`
or put in your composer.json  
```
"require": {
    "p-chess/chess": "^1.0"
}
```

## Example Code
The code below plays a complete game of chess ... randomly.

```php
<?php

require 'vendor/autoload.php';
use \PChess\Chess\Chess;
use \PChess\Chess\Output\UnicodeOutput;

$chess = new Chess();
while (!$chess->gameOver()) {
    $moves = $chess->moves();
    $move = $moves[random_int(0, count($moves) - 1)];
    $chess->move($move);
}

echo (new UnicodeOutput())->render($chess) . PHP_EOL;
```

```
   +---+---+---+---+---+---+---+---+
 8 |   | ♜ | ♘ |   |   |   |   |   | 
   +---+---+---+---+---+---+---+---+
 7 | ♞ |   |   |   |   |   |   |   | 
   +---+---+---+---+---+---+---+---+
 6 |   |   |   |   |   |   |   |   | 
   +---+---+---+---+---+---+---+---+
 5 |   |   |   |   |   |   |   |   | 
   +---+---+---+---+---+---+---+---+
 4 |   |   |   |   |   | ♚ | ♟ |   | 
   +---+---+---+---+---+---+---+---+
 3 | ♜ |   |   |   |   |   |   |   | 
   +---+---+---+---+---+---+---+---+
 2 |   |   |   |   |   |   |   |   | 
   +---+---+---+---+---+---+---+---+
 1 | ♔ |   |   |   | ♞ |   |   |   | 
   +---+---+---+---+---+---+---+---+
     a   b   c   d   e   f   g   h
```

## Supported output formats

### ASCII

Pieces are displayed with corresponding codes (e.g. "p" for pawn, "q" for queen, etc.).

```
<?php
// use...
$chess = new Chess();
echo (new AsciiOutput())->render($chess);
```

### Unicode

Pieces are displayed like in the example above.

```
<?php
// use...
$chess = new Chess();
echo (new UnicodeOutput())->render($chess);
```

### PNG Image

This opion requires [Imagine library](https://packagist.org/packages/imagine/imagine).

You need to provide an instance of Imagine, and a path of images for pieces.
Names of such images need to match a color/name code.
For example, a black pawn image must be named `bp.png` (_b_ for black and _p_ for pawn), a
white queen image must me named `wq.png` (_w_ for white and _q_ for queen).
Refer to `Piece` class source code to get all abbreviations.
A good source for free images is [Wikimedia](https://commons.wikimedia.org/wiki/Category:SVG_chess_pieces).

Other possible arguments are: a color for light squares, a color for dark squares, and a size for the board
(must be a number divisible by 8, default is 400).

```
<?php
// use...
$chess = new Chess();
$imagine = new \Imagine\Gd\Imagine();   // or \Imagine\Imagick\Imagine()
$output = new ImageOutput($imagine, '/your/path/to/images', 480);
header('Content-Type: image/png');  
echo $output->render($chess);
```

<img src="https://user-images.githubusercontent.com/179866/112304837-411be280-8c9e-11eb-8333-c2489f9bef05.png">        

## Performance

There is still a lot to do in this topic.  
[akondas/php-grandmaster](https://github.com/akondas/php-grandmaster) is a good place to start experiment ;)

### Chess::move()

| iteration | mean  | comment |
| :-------: | :---: | ------- |
| 1 | 548.819μs | initial |
| 2 | 447.973μs | replace fen with json_encode in history position (inThreefoldRepetition cache)
| 3 | 340.375μs | replace fen with json_encode in generateMoves
| 4 | 333.145μs | add boardHash calculation on make/undo move
| 5 | 25.917μs | :fire: add cache for moveToSAN method 
