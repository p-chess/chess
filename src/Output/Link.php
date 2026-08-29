<?php

declare(strict_types=1);

namespace PChess\Chess\Output;

/**
 * This is a simple value object used by HtmlOutput to represent
 * a CSS class and a URL (both can be null).
 * A link can be flagged as "unsafe", meaning that following it changes the state
 * of the game: such links are rendered as submit buttons of a POST form,
 * instead of plain anchors.
 */
final class Link
{
    public function __construct(public ?string $class, public ?string $url, public bool $unsafe = false)
    {
    }

    public function getClass(): ?string
    {
        return null !== $this->class ? ' class="'.$this->class.'"' : null;
    }

    public function getUrl(): ?string
    {
        return null !== $this->url ? ' href="'.$this->url.'"' : null;
    }

    public function getFormAction(): ?string
    {
        return null !== $this->url ? ' formaction="'.$this->url.'"' : null;
    }
}
