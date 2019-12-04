<?php

namespace Tests\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Markdown;

class MarkdownTest extends TestCase
{
    /** @test */
    function it_augments_to_html()
    {
        $this->assertEquals(
            '<p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p>',
            $this->fieldtype()->augment('Paragraph with **bold** and _italic_ text.')
        );
    }

    /** @test */
    function it_augments_with_smartypants()
    {
        $default = $this->fieldtype();
        $this->assertEquals('<p>Some "quoted" text.</p>', $default->augment('Some "quoted" text.'));

        $enabled = $this->fieldtype(['smartypants' => true]);
        $this->assertEquals('<p>Some &#8220;quoted&#8221; text.</p>', $enabled->augment('Some "quoted" text.'));

        $disabled = $this->fieldtype(['smartypants' => false]);
        $this->assertEquals('<p>Some "quoted" text.</p>', $disabled->augment('Some "quoted" text.'));
    }

    /** @test */
    function it_converts_to_smartypants_after_html()
    {
        $md = $this->fieldtype(['smartypants' => true]);

        $value = <<<EOT
Paragraph with `some code`.

Paragraph that hasn't got any "code".

``` js
code block
```
EOT;

        $expected = <<<EOT
<p>Paragraph with <code>some code</code>.</p>
<p>Paragraph that hasn&#8217;t got any &#8220;code&#8221;.</p>
<pre><code class="language-js">code block</code></pre>
EOT;

        $this->assertEquals($expected, $md->augment($value));
    }

    /** @test */
    function it_can_add_links_automatically_when_augmenting()
    {
        $value = 'before http://example.com after';
        $replaced = '<p>before <a href="http://example.com">http://example.com</a> after</p>';
        $unreplaced = '<p>before http://example.com after</p>';

        $default = $this->fieldtype();
        $this->assertEquals($unreplaced, $default->augment($value));

        $enabled = $this->fieldtype(['automatic_links' => true]);
        $this->assertEquals($replaced, $enabled->augment($value));

        $disabled = $this->fieldtype(['automatic_links' => false]);
        $this->assertEquals($unreplaced, $disabled->augment($value));
    }

    /** @test */
    function it_can_escape_markup_when_augmenting()
    {
        $value = 'before <div>in the div</div> after';
        $escaped = '<p>before &lt;div&gt;in the div&lt;/div&gt; after</p>';
        $unescaped = '<p>before <div>in the div</div> after</p>';

        $default = $this->fieldtype();
        $this->assertEquals($unescaped, $default->augment($value));

        $enabled = $this->fieldtype(['escape_markup' => true]);
        $this->assertEquals($escaped, $enabled->augment($value));

        $disabled = $this->fieldtype(['escape_markup' => false]);
        $this->assertEquals($unescaped, $disabled->augment($value));
    }

    /** @test */
    function it_can_automatically_add_line_breaks_when_augmenting()
    {
        $value = <<<EOT
first line
second line
EOT;

        $withBreaks = <<<EOT
<p>first line<br />
second line</p>
EOT;

        $withoutBreaks = <<<EOT
<p>first line
second line</p>
EOT;

        $default = $this->fieldtype();
        $this->assertEquals($withoutBreaks, $default->augment($value));

        $enabled = $this->fieldtype(['automatic_line_breaks' => true]);
        $this->assertEquals($withBreaks, $enabled->augment($value));

        $disabled = $this->fieldtype(['automatic_line_breaks' => false]);
        $this->assertEquals($withoutBreaks, $disabled->augment($value));
    }

    private function fieldtype($config = [])
    {
        return (new Markdown)->setField(new Field('test', array_merge(['type' => 'markdown'], $config)));
    }
}
