# Chess

Chess is a PHP chess library that is used for chess move
generation/validation, piece placement/movement, and check/checkmate/stalemate
detection - basically everything but the AI. 

NOTE: this started as a port of [chess.js](https://github.com/jhlywa/chess.js) for php, froked from [ryanhs/chess.php](https://github.com/ryanhs/chess.php)

[![Latest Stable Version](https://poser.pugx.org/p-chess/chess/v/stable)](https://packagist.org/p-chess/chess)
[![Build Status](https://travis-ci.org/p-chess/chess.svg?branch=master)](https://travis-ci.org/p-chess/chess)
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

$chess = new Chess();
while (!$chess->gameOver()) {
	$moves = $chess->moves();
	$move = $moves[mt_rand(0, count($moves) - 1)];
	$chess->move($move);
}

echo $chess->ascii() . PHP_EOL;
```

```
   +------------------------+
 8 | .  .  .  .  .  .  .  . |
 7 | .  .  .  .  r  .  .  . |
 6 | .  .  .  .  .  .  .  . |
 5 | .  .  .  .  .  .  .  . |
 4 | k  .  .  .  .  .  .  . |
 3 | .  .  .  .  .  .  K  . |
 2 | .  .  .  n  .  .  .  . |
 1 | .  .  .  .  .  .  .  . |
   +------------------------+
     a  b  c  d  e  f  g  h
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
