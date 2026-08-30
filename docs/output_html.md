# Output with HTML

This option requires extending the provided `HtmlOutput` class, since you need to define
a way to build links for pieces.

A link can be flagged as *unsafe*, meaning that following it changes the state of the game.
Ending a move is the only such link: it is rendered as the submit button of a `POST` form
wrapping the board, so that it cannot be triggered by a `GET` request (a prefetch, a crawler,
or a cross-site request would otherwise be enough to move a piece).
All the other links only display something, hence they stay plain anchors.

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
    public function generateLinks(Chess $chess, ?string $from = null, $identifier = null): array
    {
        $links = [];
        $allowedMoves = $this->getAllowedMoves($chess);
        /** @var int $i */
        foreach ($chess->board as $i => $piece) {
            $url = null;
            $class = null;
            $unsafe = false;
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
                            // this is the only link that changes the game
                            $url = $this->generateLinkForMoveEnd($from, $san);
                            $unsafe = true;
                        }
                    }
                    $class = 'target';
                }
            } else {
                // restart move
                $url = $this->generateLinkForMoveRestart();
                $class = 'current';
            }
            $links[$i] = new Link($class, $url, $unsafe);
        }

        return $links;
    }
}
```

If your framework requires a CSRF token (or any other hidden field) to accept the `POST`,
override `getHiddenFields()`: its entries are added to the form wrapping the board, and
escaped for you.

```php
protected function getHiddenFields($identifier = null): array
{
    return ['_token' => $this->getCsrfTokenSomehow()];
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
<!-- pawn in b2 started move: the board is wrapped in a form, since a move can end here -->
<form method="post">
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
        <td class="target"><button type="submit" formaction="/move/b2/b4"></button></td>
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
        <td class="target"><button type="submit" formaction="/move/b2/b3"></button></td>
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
</form>
```

This is a preview of HTML displayed above, with a bit of styling applied:

<img src="https://user-images.githubusercontent.com/179866/114995898-92cf1b80-9e9e-11eb-8e99-75a60bbba6bd.png" alt="">
