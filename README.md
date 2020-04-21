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
