<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class PluckIntoTest extends ParserTestCase
{
    protected $pluckData = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pluckData = [
            'topics' => [
                ['id' => 1, 'name' => 'Topic One'],
                ['id' => 2, 'name' => 'Topic Two'],
                ['id' => 3, 'name' => 'Topic Three'],
                ['id' => 4, 'name' => 'Topic Four'],
            ],
            'articles' => [
                ['id' => 1, 'name' => 'Article One', 'topics' => [1, 2, 3]],
                ['id' => 2, 'name' => 'Article Two', 'topics' => [1]],
                ['id' => 3, 'name' => 'Article Three', 'topics' => [1, 4]],
                ['id' => 4, 'name' => 'Article Four', 'topics' => [2, 5]],
                ['id' => 5, 'name' => 'Article Five', 'topics' => [1, 2]],
            ],
            'search' => [2, 3],
        ];
    }

    public function test_basic_pluck_into()
    {
        $expected = <<<'EOT'
Topic: 1 - Topic One
Var Count: 4
Calculated Count: 4
================

Article: 1 - Article One

Article: 2 - Article Two

Article: 3 - Article Three

Article: 5 - Article Five



Topic: 2 - Topic Two
Var Count: 3
Calculated Count: 3
================

Article: 1 - Article One

Article: 4 - Article Four

Article: 5 - Article Five



Topic: 3 - Topic Three
Var Count: 1
Calculated Count: 1
================

Article: 1 - Article One



Topic: 4 - Topic Four
Var Count: 1
Calculated Count: 1
================

Article: 3 - Article Three
EOT;

        $template = <<<'EOT'
{{ test = topics pluck_into articles (t, a => a.topics arr_contains t.id) }}
Topic: {{ id }} - {{ name }}
Var Count: {{ articles_count }}
Calculated Count: {{ articles | length }}
================
{{ articles }}
Article: {{ id }} - {{ name }}
{{ /articles }}

{{ /test }}
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->pluckData))));

        $template = <<<'EOT'
{{ test = topics pluck_into articles (topic, article => article.topics arr_contains topic.id) }}
Topic: {{ id }} - {{ name }}
Var Count: {{ articles_count }}
Calculated Count: {{ articles | length }}
================
{{ articles }}
Article: {{ id }} - {{ name }}
{{ /articles }}

{{ /test }}
EOT;
        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->pluckData))));

        $template = <<<'EOT'
{{ test = topics pluck_into articles (topic, article => article:topics arr_contains topic.id) }}
Topic: {{ id }} - {{ name }}
Var Count: {{ articles_count }}
Calculated Count: {{ articles | length }}
================
{{ articles }}
Article: {{ id }} - {{ name }}
{{ /articles }}

{{ /test }}
EOT;
        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->pluckData))));
    }

    public function test_basic_pluck_into_without_explicitly_naming_variables()
    {
        $expected = <<<'EOT'
Topic: 1 - Topic One
Var Count: 4
Calculated Count: 4
================

Article: 1 - Article One

Article: 2 - Article Two

Article: 3 - Article Three

Article: 5 - Article Five



Topic: 2 - Topic Two
Var Count: 3
Calculated Count: 3
================

Article: 1 - Article One

Article: 4 - Article Four

Article: 5 - Article Five



Topic: 3 - Topic Three
Var Count: 1
Calculated Count: 1
================

Article: 1 - Article One



Topic: 4 - Topic Four
Var Count: 1
Calculated Count: 1
================

Article: 3 - Article Three
EOT;

        $template = <<<'EOT'
{{ test = topics pluck_into articles (articles.topics arr_contains topics.id) }}
Topic: {{ id }} - {{ name }}
Var Count: {{ articles_count }}
Calculated Count: {{ articles | length }}
================
{{ articles }}
Article: {{ id }} - {{ name }}
{{ /articles }}

{{ /test }}
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->pluckData))));
    }
}
