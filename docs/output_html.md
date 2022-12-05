# Output with HTML

This option requires extending provided `HtmlOutput` class, since you need to define
a way to build links for pieces.

The following is an example of a possible extension.
Some methods are omitted, but names should be self-explanatory:

```php
<?php

namespace App;

use PChess\Chess\Board;
use PChess\Chess\Chess;
use PChess\Chess\Output\HtmlOutput;
use PChess\Chess\Output\Link;

final class MyHtmlOutput extends HtmlOutput
{
    public function generateLinks(Chess $chess, ?string $from = null): array
    {
        $links = [];
        $allowedMoves = $this->getAllowedMoves($chess);
        /** @var int $i */
        foreach ($chess->board as $i => $piece) {
            $url = null;
            $class = null;
            $san = Board::algebraic($i);
            if (null === $from) {
                // move not started
                if (null !== $piece && isset($allowedMoves[$san]) && $this->isTurn($chess, $piece)) {
                    $url = $this->generateLinkForMoveStart($san);
                }
            } elseif ($from !== $san) {
                // move started
                if ($this->canMove($from, $i, $allowedMoves)) {
                    if (null !== $movingPiece = $chess->board[Board::SQUARES[$from]]) {
                        if ('p' === $movingPiece->getType() && (0 === Board::rank($i) || 7 === Board::rank($i))) {
                            $url = $this->generateLinkForMovePromotion($from, $san);
                        } else {
                            $url = $this->generateLinkForMoveEnd($from, $san);
                        }
                    }
                    $class = 'target';
                }
            } else {
                // restart move
                $url = $this->generateLinkForMoveRestart();
                $class = 'current';
            }
            $links[$i] = new Link($class, $url);
        }

        return $links;
    }
}
```

You can then use it as follows

```php
<?php

use App\MyHtmlOutput;
use PChess\Chess\Chess;

$chess = new Chess();
$output = new MyHtmlOutput();
echo $output->render($chess);
```

The output is something like the following:

```html
<!-- pawn in b2 started move -->
<table id="board">
    <tr>
        <td class="file">8</td>
        <td></td>
        <td></td>
        <td><a class="bk"></a></td>
        <td><a class="br"></a></td>
        <td></td>
        <td></td>
        <td><a class="bn"></a></td>
        <td><a class="br"></a></td>
    </tr>
    <tr>
        <td class="file">7</td>
        <td><a class="bp"></a></td>
        <td><a class="bp"></a></td>
        <td><a class="bp"></a></td>
        <td><a class="bq"></a></td>
        <td><a class="bb"></a></td>
        <td><a class="bp"></a></td>
        <td><a class="bp"></a></td>
        <td><a class="bp"></a></td>
    </tr>
    <tr>
        <td class="file">6</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><a class="bb"></a></td>
        <td></td>
    </tr>
    <tr>
        <td class="file">5</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><a class="bp"></a></td>
        <td></td>
        <td><a class="wb"></a></td>
        <td></td>
    </tr>
    <tr>
        <td class="file">4</td>
        <td></td>
        <td class="target"><a href="/move/b2/b4"></a></td>
        <td><a class="wn"></a></td>
        <td><a class="bn"></a></td>
        <td><a class="wp"></a></td>
        <td></td>
        <td><a class="wp"></a></td>
        <td></td>
    </tr>
    <tr>
        <td class="file">3</td>
        <td></td>
        <td class="target"><a href="/move/b2/b3"></a></td>
        <td></td>
        <td><a class="wp"></a></td>
        <td></td>
        <td></td>
        <td></td>
        <td><a class="wp"></a></td>
    </tr>
    <tr>
        <td class="file">2</td>
        <td><a class="wp"></a></td>
        <td class="current"><a class="wp" href="/"></a></td>
        <td><a class="wp"></a></td>
        <td></td>
        <td><a class="wb"></a></td>
        <td><a class="wp"></a></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="file">1</td>
        <td><a class="wr"></a></td>
        <td></td>
        <td></td>
        <td><a class="wq"></a></td>
        <td></td>
        <td><a class="wr"></a></td>
        <td><a class="wk"></a></td>
        <td></td>
    </tr>
    <tr>
        <td class="file"></td>
        <td class="rank">a</td>
        <td class="rank">b</td>
        <td class="rank">c</td>
        <td class="rank">d</td>
        <td class="rank">e</td>
        <td class="rank">f</td>
        <td class="rank">g</td>
        <td class="rank">h</td>
    </tr>
</table>
```

This is a preview of HTML displayed above, with a bit of styling applied:

<img src="https://user-images.githubusercontent.com/179866/114995898-92cf1b80-9e9e-11eb-8e99-75a60bbba6bd.png" alt="">
