<?php

declare(strict_types=1);

namespace PChess\Chess\Test;

use PChess\Chess\Chess;
use PHPUnit\Framework\TestCase;

final class HtmlOutputTest extends TestCase
{
    public function testSafeLinksAreRenderedAsAnchors(): void
    {
        $rendered = (new HtmlOutputStub())->render(new Chess());

        self::assertStringStartsWith('<table id="board">', $rendered, 'no unsafe link, hence no form is needed');
        self::assertStringEndsWith('</table>', $rendered);
        self::assertStringNotContainsString('<button', $rendered);
    }

    public function testUnsafeLinksAreRenderedAsSubmitButtons(): void
    {
        $rendered = (new UnsafeHtmlOutputStub())->render(new Chess());

        self::assertStringStartsWith('<form method="post">', $rendered);
        self::assertStringEndsWith('</form>', $rendered);
        self::assertStringContainsString(
            '<td class="target"><button type="submit" formaction="/move/e2/e4"></button></td>',
            $rendered,
        );
        // a link that only displays something stays an anchor
        self::assertStringContainsString('<td class="current"><a class="wp" href="/cancel"></a></td>', $rendered);
    }

    public function testHiddenFieldsAreAddedToTheFormAndEscaped(): void
    {
        $rendered = (new UnsafeHtmlOutputStub())->render(new Chess());

        self::assertStringContainsString('<input type="hidden" name="_token" value="a&amp;token">', $rendered);
    }
}
