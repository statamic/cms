<?php

namespace Tests\Markdown;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Markdown;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    public function assertParses($expectedHtml, $markdown)
    {
        $this->assertEquals($expectedHtml, rtrim(Markdown::parse($markdown)));
    }

    #[Test]
    public function it_parses_markdown()
    {
        $this->assertParses('<h1>Heading One</h1>', '# Heading One');
    }

    #[Test]
    public function it_parses_strikethrough()
    {
        $this->assertParses('<h1>Heading <del>One</del></h1>', '# Heading ~~One~~');
    }

    #[Test]
    public function it_parses_markdown_inside_markup()
    {
        $markdown = <<<'EOT'
<div>

# Heading

</div>

## Another Heading
EOT;

        $html = <<<'EOT'
<div>
<h1>Heading</h1>
</div>
<h2>Another Heading</h2>
EOT;

        $this->assertParses($html, $markdown);
    }

    #[Test]
    public function it_parses_attributes()
    {
        $this->assertParses(
            '<h2 class="main header" id="the-heading" lang="en">Heading</h2>',
            '## Heading {.main .header #the-heading lang=en}'
        );
    }

    #[Test]
    public function it_parses_code_blocks()
    {
        $markdown = <<<'EOT'
# Heading

``` yaml
foo: bar
```

Paragraph
EOT;

        $html = <<<'EOT'
<h1>Heading</h1>
<pre><code class="language-yaml">foo: bar
</code></pre>
<p>Paragraph</p>
EOT;
        $this->assertParses($html, $markdown);
    }

    #[Test]
    public function it_parses_tables()
    {
        $markdown = <<<'EOT'
# Heading

| Header One | Header Two |
|-----|-----|
| 1.1 | 1.2 |
| 2.1 | 2.2 |

Paragraph
EOT;

        $html = <<<'EOT'
<h1>Heading</h1>
<table>
<thead>
<tr>
<th>Header One</th>
<th>Header Two</th>
</tr>
</thead>
<tbody>
<tr>
<td>1.1</td>
<td>1.2</td>
</tr>
<tr>
<td>2.1</td>
<td>2.2</td>
</tr>
</tbody>
</table>
<p>Paragraph</p>
EOT;

        $this->assertParses($html, $markdown);
    }

    #[Test]
    public function it_parses_description_lists()
    {
        $markdown = <<<'EOT'
# Heading

Apple
:   Pomaceous fruit of plants of the genus Malus in
    the family Rosaceae.
:   An American computer company.

Orange
:   The fruit of an evergreen tree of the genus Citrus.

Paragraph
EOT;

        $html = <<<'EOT'
<h1>Heading</h1>
<dl>
<dt>Apple</dt>
<dd>Pomaceous fruit of plants of the genus Malus in
the family Rosaceae.</dd>
<dd>An American computer company.</dd>
<dt>Orange</dt>
<dd>The fruit of an evergreen tree of the genus Citrus.</dd>
</dl>
<p>Paragraph</p>
EOT;
        $this->assertParses($html, $markdown);
    }

    #[Test]
    public function it_parses_footnotes()
    {
        $markdown = <<<'EOT'
This is a true fact[^note1]. Seriously.

[^note1]: Someone said this, probably.
EOT;

        $html = <<<'EOT'
<p>This is a true fact<sup id="fnref:note1"><a class="footnote-ref" href="#fn:note1" role="doc-noteref">1</a></sup>. Seriously.</p>
<div class="footnotes" role="doc-endnotes"><hr /><ol><li class="footnote" id="fn:note1" role="doc-endnote"><p>Someone said this, probably.&nbsp;<a class="footnote-backref" rev="footnote" href="#fnref:note1" role="doc-backlink">↩</a></p></li></ol></div>
EOT;
        $this->assertParses($html, $markdown);
    }

    #[Test]
    public function it_parses_tasklists()
    {
        $markdown = <<<'EOT'
# Heading

- [x] One
- [ ] Two
- [ ] Three

Paragraph
EOT;

        $html = <<<'EOT'
<h1>Heading</h1>
<ul>
<li><input checked="" disabled="" type="checkbox"> One</li>
<li><input disabled="" type="checkbox"> Two</li>
<li><input disabled="" type="checkbox"> Three</li>
</ul>
<p>Paragraph</p>
EOT;
        $this->assertParses($html, $markdown);
    }

    #[Test]
    public function it_does_not_automatically_convert_urls_to_links()
    {
        $this->assertParses('<p>https://example.com</p>', 'https://example.com');

        $this->assertEquals(
            '<p><a href="https://example.com">https://example.com</a></p>',
            rtrim(Markdown::withAutoLinks()->parse('https://example.com'))
        );
    }

    #[Test]
    public function it_converts_line_breaks_on_demand()
    {
        $this->assertParses("<p>foo\nbar</p>", "foo\nbar");

        $this->assertEquals(
            "<p>foo<br />\nbar</p>",
            rtrim(Markdown::withAutoLineBreaks()->parse("foo\nbar"))
        );
    }

    #[Test]
    public function it_escapes_markup_on_demand()
    {
        $this->assertParses('<div></div>', '<div></div>');

        $this->assertEquals(
            '&lt;div&gt;&lt;/div&gt;',
            rtrim(Markdown::withMarkupEscaping()->parse('<div></div>'))
        );
    }

    #[Test]
    public function it_uses_smart_punctuation_on_demand()
    {
        $this->assertParses('<p>&quot;Foo&quot; -- Bar...</p>', '"Foo" -- Bar...');

        $this->assertEquals(
            '<p>“Foo” – Bar…</p>',
            rtrim(Markdown::withSmartPunctuation()->parse('"Foo" -- Bar...'))
        );
    }

    #[Test]
    public function it_uses_heading_permalinks_on_demand()
    {
        $markdown = <<<'EOT'
## Alfa Bravo
## Charlie Delta
EOT;

        $this->assertParses(<<<'EOT'
<h2>Alfa Bravo</h2>
<h2>Charlie Delta</h2>
EOT, $markdown);

        $this->assertEquals(<<<'EOT'
<h2><a id="content-alfa-bravo" href="#content-alfa-bravo" class="heading-permalink" aria-hidden="true" title="Permalink">¶</a>Alfa Bravo</h2>
<h2><a id="content-charlie-delta" href="#content-charlie-delta" class="heading-permalink" aria-hidden="true" title="Permalink">¶</a>Charlie Delta</h2>
EOT,
            rtrim(Markdown::withHeadingPermalinks()->parse($markdown))
        );
    }

    #[Test]
    public function it_uses_table_of_contents_on_demand()
    {
        $markdown = <<<'EOT'
## Alfa Bravo
Foo bar.
## Charlie Delta
Baz qux.
EOT;

        $this->assertParses(<<<'EOT'
<h2>Alfa Bravo</h2>
<p>Foo bar.</p>
<h2>Charlie Delta</h2>
<p>Baz qux.</p>
EOT, $markdown);

        $expected = <<<'EOT'
<ul class="table-of-contents">
<li>
<a href="#content-alfa-bravo">Alfa Bravo</a>
</li>
<li>
<a href="#content-charlie-delta">Charlie Delta</a>
</li>
</ul>
<h2><a id="content-alfa-bravo" href="#content-alfa-bravo" class="heading-permalink" aria-hidden="true" title="Permalink">¶</a>Alfa Bravo</h2>
<p>Foo bar.</p>
<h2><a id="content-charlie-delta" href="#content-charlie-delta" class="heading-permalink" aria-hidden="true" title="Permalink">¶</a>Charlie Delta</h2>
<p>Baz qux.</p>
EOT;

        // Make assertion without newlines because they differ between versions of commonmark.
        $this->assertEquals(
            str($expected)->replace("\n", ''),
            str(Markdown::withTableOfContents()->parse($markdown))->trim()->replace("\n", '')
        );
    }
}
