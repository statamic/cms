<?php

namespace Tests\Fieldtypes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Markdown;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_augments_to_html()
    {
        $this->assertEqualsTrimmed(
            '<p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p>',
            $this->fieldtype()->augment('Paragraph with **bold** and _italic_ text.')
        );
    }

    #[Test]
    public function it_returns_null_on_null_value()
    {
        $this->assertSame(
            null,
            $this->fieldtype()->augment(null)
        );
    }

    #[Test]
    public function it_augments_with_smartypants()
    {
        $default = $this->fieldtype();
        $this->assertEqualsTrimmed('<p>Some &quot;quoted&quot; text.</p>', $default->augment('Some "quoted" text.'));

        $enabled = $this->fieldtype(['smartypants' => true]);
        $this->assertEqualsTrimmed('<p>Some “quoted” text.</p>', $enabled->augment('Some "quoted" text.'));

        $disabled = $this->fieldtype(['smartypants' => false]);
        $this->assertEqualsTrimmed('<p>Some &quot;quoted&quot; text.</p>', $disabled->augment('Some "quoted" text.'));
    }

    #[Test]
    public function it_converts_to_smartypants_after_html()
    {
        $md = $this->fieldtype(['smartypants' => true]);

        $value = <<<'EOT'
Paragraph with `some code`.

Paragraph that hasn't got any "code".

``` js
code block
```
EOT;

        $expected = <<<'EOT'
<p>Paragraph with <code>some code</code>.</p>
<p>Paragraph that hasn’t got any “code”.</p>
<pre><code class="language-js">code block
</code></pre>
EOT;

        $this->assertEqualsTrimmed($expected, $md->augment($value));
    }

    #[Test]
    public function it_can_add_links_automatically_when_augmenting()
    {
        $value = 'before http://example.com after';
        $replaced = '<p>before <a href="http://example.com">http://example.com</a> after</p>';
        $unreplaced = '<p>before http://example.com after</p>';

        $default = $this->fieldtype();
        $this->assertEqualsTrimmed($unreplaced, $default->augment($value));

        $enabled = $this->fieldtype(['automatic_links' => true]);
        $this->assertEqualsTrimmed($replaced, $enabled->augment($value));

        $disabled = $this->fieldtype(['automatic_links' => false]);
        $this->assertEqualsTrimmed($unreplaced, $disabled->augment($value));
    }

    #[Test]
    public function it_can_escape_markup_when_augmenting()
    {
        $value = 'before <div>in the div</div> after';
        $escaped = '<p>before &lt;div&gt;in the div&lt;/div&gt; after</p>';
        $unescaped = '<p>before <div>in the div</div> after</p>';

        $default = $this->fieldtype();
        $this->assertEqualsTrimmed($unescaped, $default->augment($value));

        $enabled = $this->fieldtype(['escape_markup' => true]);
        $this->assertEqualsTrimmed($escaped, $enabled->augment($value));

        $disabled = $this->fieldtype(['escape_markup' => false]);
        $this->assertEqualsTrimmed($unescaped, $disabled->augment($value));
    }

    #[Test]
    public function it_can_automatically_add_line_breaks_when_augmenting()
    {
        $value = <<<'EOT'
first line
second line
EOT;

        $withBreaks = <<<'EOT'
<p>first line<br />
second line</p>
EOT;

        $withoutBreaks = <<<'EOT'
<p>first line
second line</p>
EOT;

        $default = $this->fieldtype();
        $this->assertEqualsTrimmed($withBreaks, $default->augment($value));

        $enabled = $this->fieldtype(['automatic_line_breaks' => true]);
        $this->assertEqualsTrimmed($withBreaks, $enabled->augment($value));

        $disabled = $this->fieldtype(['automatic_line_breaks' => false]);
        $this->assertEqualsTrimmed($withoutBreaks, $disabled->augment($value));
    }

    #[Test]
    public function it_converts_statamic_asset_urls()
    {
        Storage::fake('test', ['url' => '/assets']);
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');

        tap(Facades\AssetContainer::make()->handle('test_container')->disk('test'))->save();
        tap(Facades\Asset::make()->container('test_container')->path('foo/hoff.jpg'))->save();

        $markdown = <<<'EOT'
# Actual asset...
[link](statamic://asset::test_container::foo/hoff.jpg)
![](statamic://asset::test_container::foo/hoff.jpg)
<img src="statamic://asset::test_container::foo/hoff.jpg" alt="Asset" />

# Non-existent asset...
[link](statamic://asset::test_container::nope.jpg)
![](statamic://asset::test_container::nope.jpg)
<a href="statamic://asset::test_container::nope.jpg">Asset Link</a>
EOT;

        $expected = <<<'EOT'
<h1>Actual asset...</h1>
<p><a href="/assets/foo/hoff.jpg">link</a><br />
<img src="/assets/foo/hoff.jpg" alt="" /><br />
<img src="/assets/foo/hoff.jpg" alt="Asset" /></p>
<h1>Non-existent asset...</h1>
<p><a href="">link</a><br />
<img src="" alt="" /><br />
<a href="">Asset Link</a></p>

EOT;

        $this->assertEquals($expected, $this->fieldtype()->augment($markdown));
    }

    private function fieldtype($config = [])
    {
        return (new Markdown)->setField(new Field('test', array_merge(['type' => 'markdown'], $config)));
    }

    private function assertEqualsTrimmed($expected, $actual)
    {
        $this->assertEquals($expected, rtrim($actual));
    }
}
