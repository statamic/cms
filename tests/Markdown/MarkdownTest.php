<?php

namespace Tests\Markdown;

use Statamic\Facades\Markdown;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    public function assertParses($expectedHtml, $markdown)
    {
        $this->assertEquals($expectedHtml, rtrim(Markdown::parse($markdown)));
    }

    /** @test */
    public function it_parses_markdown()
    {
        $this->assertParses('<h1>Heading One</h1>', '# Heading One');
    }

    /** @test */
    public function it_parses_strikethrough()
    {
        $this->assertParses('<h1>Heading <del>One</del></h1>', '# Heading ~~One~~');
    }

    /** @test */
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

    /** @test */
    public function it_parses_attributes()
    {
        $this->assertParses(
            '<h2 class="main header" id="the-heading" lang="en">Heading</h2>',
            '## Heading {.main .header #the-heading lang=en}'
        );
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_does_not_automatically_convert_urls_to_links()
    {
        $this->assertParses('<p>https://example.com</p>', 'https://example.com');

        $this->assertEquals(
            '<p><a href="https://example.com">https://example.com</a></p>',
            rtrim(Markdown::withAutoLinks()->parse('https://example.com'))
        );
    }

    /** @test */
    public function it_converts_line_breaks_on_demand()
    {
        $this->assertParses("<p>foo\nbar</p>", "foo\nbar");

        $this->assertEquals(
            "<p>foo<br />\nbar</p>",
            rtrim(Markdown::withAutoLineBreaks()->parse("foo\nbar"))
        );
    }

    /** @test */
    public function it_escapes_markup_on_demand()
    {
        $this->assertParses('<div></div>', '<div></div>');

        $this->assertEquals(
            '&lt;div&gt;&lt;/div&gt;',
            rtrim(Markdown::withMarkupEscaping()->parse('<div></div>'))
        );
    }

    /** @test */
    public function it_uses_smart_punctuation_on_demand()
    {
        $this->assertParses('<p>&quot;Foo&quot; -- Bar...</p>', '"Foo" -- Bar...');

        $this->assertEquals(
            '<p>“Foo” – Bar…</p>',
            rtrim(Markdown::withSmartPunctuation()->parse('"Foo" -- Bar...'))
        );
    }
}
