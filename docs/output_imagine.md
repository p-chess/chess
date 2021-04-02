# Output with Imagine

This option requires [Imagine library](https://packagist.org/packages/imagine/imagine)
and one extension between GD and Imagick.

You need to provide an instance of Imagine, and a path of images for pieces.
Names of such images need to match a color/name code.
For example, a black pawn image must be named `bp.png` (_b_ for black and _p_ for pawn), a
white queen image must me named `wq.png` (_w_ for white and _q_ for queen).
Refer to `Piece` class source code to get all abbreviations.
A good source for free images is [Wikimedia](https://commons.wikimedia.org/wiki/Category:SVG_chess_pieces).

```php
<?php
// use...
$chess = new Chess();
$imagine = new \Imagine\Gd\Imagine();   // or \Imagine\Imagick\Imagine()
$output = new ImageOutput($imagine, '/your/path/to/images');
header('Content-Type: image/png');  
echo $output->render($chess);
```

<img src="https://user-images.githubusercontent.com/179866/112304837-411be280-8c9e-11eb-8333-c2489f9bef05.png">     

If you want to also display coordinates, you'll also need a font file, named `font.ttf` and placed in the
same resource directory seen above.

Just pass a fourth parameter `true`:

```php
<?php
// use...
$chess = new Chess();
$imagine = new \Imagine\Gd\Imagine();   // or \Imagine\Imagick\Imagine()
$output = new ImageOutput($imagine, '/your/path/to/images', 480, true);
header('Content-Type: image/png');  
echo $output->render($chess);
```

<img src="https://user-images.githubusercontent.com/179866/113125801-4685a980-9217-11eb-9e0a-0acf54c4ea88.png">

A complete list of arguments:

```php
public function __construct(
    AbstractImagine $imagine,
    string $resourcesPath,
    int $size = 400,    // this MUST be divisible by 4
    bool $coords = false,
    string $darkSquareColor = '#8ca2ad',
    string $liteSquareColor = '#dee3e6'
);
```

In this directory, you can find an utility script, named [get-resources.sh](get-resources.sh), that downloads
all pieces images from Wikimedia, plus a free font to use.
