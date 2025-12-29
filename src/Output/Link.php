<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

/**
 * This is a simple value object used by HtmlOutput to represent
 * a CSS class and a URL (both can be null).
 */
final class Link
{
    public function __construct(public ?string $class, public ?string $url)
    {
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
