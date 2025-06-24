# Chess

Chess is a PHP chess library used for chess move
generation/validation, piece placement/movement, and check/checkmate/stalemate
detection - basically everything but the AI. 

NOTE: this started as a port of [chess.js](https://github.com/jhlywa/chess.js) for PHP, forked from [ryanhs/chess.php](https://github.com/ryanhs/chess.php)

[![Latest Stable Version](https://poser.pugx.org/p-chess/chess/v/stable)](https://packagist.org/p-chess/chess)
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

```php
<?php
// use...
$chess = new Chess();
echo (new AsciiOutput())->render($chess);
```

### Unicode

Pieces are displayed like in the example above.

```php
<?php
// use...
$chess = new Chess();
echo (new UnicodeOutput())->render($chess);
```

### PNG Image

Pieces are displayed inside a png image.

```php
<?php
// use...
$chess = new Chess();
$imagine = new \Imagine\Gd\Imagine();   // or \Imagine\Imagick\Imagine()
$output = new ImageOutput($imagine, '/your/path/to/images', 480);
header('Content-Type: image/png');  
echo $output->render($chess);
```

<img src="https://user-images.githubusercontent.com/179866/112304837-411be280-8c9e-11eb-8333-c2489f9bef05.png">        

See [dedicated documentation](docs/output_imagine.md) for detailed instructions.

### SVG Image

Pieces are displayed inside an SVG image.

```php
<?php
// use...
$chess = new Chess();
$output = new SvgOutput();
echo $output->render($chess);
```

<img src="https://private-user-images.githubusercontent.com/179866/457647250-34bae1e1-83c5-47b1-a997-0b5bf4041d2a.png?jwt=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJnaXRodWIuY29tIiwiYXVkIjoicmF3LmdpdGh1YnVzZXJjb250ZW50LmNvbSIsImtleSI6ImtleTUiLCJleHAiOjE3NTA1OTU5NTgsIm5iZiI6MTc1MDU5NTY1OCwicGF0aCI6Ii8xNzk4NjYvNDU3NjQ3MjUwLTM0YmFlMWUxLTgzYzUtNDdiMS1hOTk3LTBiNWJmNDA0MWQyYS5wbmc_WC1BbXotQWxnb3JpdGhtPUFXUzQtSE1BQy1TSEEyNTYmWC1BbXotQ3JlZGVudGlhbD1BS0lBVkNPRFlMU0E1M1BRSzRaQSUyRjIwMjUwNjIyJTJGdXMtZWFzdC0xJTJGczMlMkZhd3M0X3JlcXVlc3QmWC1BbXotRGF0ZT0yMDI1MDYyMlQxMjM0MThaJlgtQW16LUV4cGlyZXM9MzAwJlgtQW16LVNpZ25hdHVyZT00ZTU3MjY5NzRlYzA3ZjNlZWQwOGNkYWNmNjVlNTA1OWY2MzNjMDg0Y2U1MjAwODNkZWIxZThhNzEyNTljMWM5JlgtQW16LVNpZ25lZEhlYWRlcnM9aG9zdCJ9.J5khvbyPEh86N9no_JVcuWumIj-gVHEOrDjIvc-wBfw">

### HTML

Pieces are displayed inside an HTML table.

See [dedicated documentation](docs/output_html.md) for detailed instructions.

## Performance

There is still a lot to do in this topic.  
[akondas/php-grandmaster](https://github.com/akondas/php-grandmaster) is a good place to start experiment ;)

### Chess::move()

| iteration |   mean    | comment                                                                        |
|:---------:|:---------:|--------------------------------------------------------------------------------|
|     1     | 548.819μs | initial                                                                        |
|     2     | 447.973μs | replace fen with json_encode in history position (inThreefoldRepetition cache) |
|     3     | 340.375μs | replace fen with json_encode in generateMoves                                  |
|     4     | 333.145μs | add boardHash calculation on make/undo move                                    |
|     5     | 25.917μs  | :fire: add cache for moveToSAN method                                          |

## Other documentation

All classes are documented in the [docs directory](docs/index.md).

