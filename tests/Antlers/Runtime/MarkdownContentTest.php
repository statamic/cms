<?php

namespace Tests\Antlers\Runtime;

use Statamic\Fields\Value;
use Statamic\Fieldtypes\Markdown;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class MarkdownContentTest extends ParserTestCase
{
    public function test_markdown_content_is_not_parsed_as_antlers()
    {
        $content = <<<'EOT'
<div class="max-w-7xl mx-auto my-30">
    {{ markdown }}
</div>
EOT;

        $content = StringUtilities::normalizeLineEndings($content);

        $markdownFieldType = new Markdown();
        $markdownField = new Value($content, 'markdown', $markdownFieldType);

        $this->assertSame($content, trim($this->renderString('{{ markdown }}', ['markdown' => $markdownField])));

        $content = <<<'EOT'
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate
agna vitae augue venenatis, vel dictum augue molestie. Aliquam ultrices hendrerit
turpis sit amet eleifend. Ut massa lorem

```html
{{ article.status }}              <!-- "published" -->
{{ article.status | capitalize }} <!-- "Published" -->
```

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate
agna vitae augue venenatis, vel dictum augue molestie. Aliquam ultrices hendrerit
turpis sit amet eleifend. Ut massa lorem
EOT;

        $result = <<<'EOT'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate
agna vitae augue venenatis, vel dictum augue molestie. Aliquam ultrices hendrerit
turpis sit amet eleifend. Ut massa lorem</p>
<pre><code class="language-html">{{ article.status }}              &lt;!-- &quot;published&quot; --&gt;
{{ article.status | capitalize }} &lt;!-- &quot;Published&quot; --&gt;
</code></pre>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate
agna vitae augue venenatis, vel dictum augue molestie. Aliquam ultrices hendrerit
turpis sit amet eleifend. Ut massa lorem</p>
EOT;

        $content = StringUtilities::normalizeLineEndings($content);
        $result = StringUtilities::normalizeLineEndings($result);

        $markdownFieldType = new Markdown();
        $markdownField = new Value($content, 'markdown', $markdownFieldType);
        $this->assertSame($result, trim($this->renderString('{{ markdown }}', ['markdown' => $markdownField])));
    }
}
