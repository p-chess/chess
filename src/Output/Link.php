<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

/**
 * This is a simple value object used by HtmlOutput to represent
 * a CSS class and an URL (both can be null).
 */
final class Link
{
    /** @var string|null */
    private $class;

    /** @var string|null */
    private $url;

    public function __construct(?string $class, ?string $url)
    {
        $this->class = $class;
        $this->url = $url;
    }

    public function getClass(): ?string
    {
        return $this->class ? ' class="'.$this->class.'"' : null;
    }

    public function getUrl(): ?string
    {
        return $this->url ? ' href="'.$this->url.'"' : null;
    }
}
