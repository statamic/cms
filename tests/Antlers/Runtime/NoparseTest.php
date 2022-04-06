<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class NoparseTest extends ParserTestCase
{
    public function test_noparse_ignores_braces_entirely()
    {
        $template = <<<'EOT'
{{ noparse }}A
instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: `
        <div>
          <div class="hit-name">
            <a href="{{url}}">{{#helpers.highlight}}{ "attribute": "name" }{{/helpers.highlight}}</a>
            <p>{{ description }}</p>
            <p>Price: \${{ price }}</p>
          </div>
        </div>
      `,
      empty: 'No results for <q>{{ query }}</q>',
    }
}),
Z{{ /noparse }}A
EOT;

        $expected = <<<'EOT'
A
instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: `
        <div>
          <div class="hit-name">
            <a href="{{url}}">{{#helpers.highlight}}{ "attribute": "name" }{{/helpers.highlight}}</a>
            <p>{{ description }}</p>
            <p>Price: \${{ price }}</p>
          </div>
        </div>
      `,
      empty: 'No results for <q>{{ query }}</q>',
    }
}),
ZA
EOT;

        $this->assertSame($expected, StringUtilities::normalizeLineEndings(trim($this->renderString($template))));
    }

    public function test_multiple_noparse_regions()
    {
        $template = <<<'EOT'
{{ noparse }}A
instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: `
        <div>
          <div class="hit-name">
            <a href="{{url}}">{{#helpers.highlight}}{ "attribute": "name" }{{/helpers.highlight}}</a>
            <p>{{ description }}</p>
            <p>Price: \${{ price }}</p>
          </div>
        </div>
      `,
      empty: 'No results for <q>{{ query }}</q>',
    }
}),
Z{{ /noparse }}A

{{ title }}

AB
{{ noparse }}A
instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: `
        <div>
          <div class="hit-name">
            <a href="{{url}}">{{#helpers.highlight}}{ "attribute": "name" }{{/helpers.highlight}}</a>
            <p>{{ description }}</p>
            <p>Price: \${{ price }}</p>
          </div>
        </div>
      `,
      empty: 'No results for <q>{{ query }}</q>',
    }
}),
Z{{/noparse }}A

{{ title }}
EOT;

        $expected = <<<'EOT'
A
instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: `
        <div>
          <div class="hit-name">
            <a href="{{url}}">{{#helpers.highlight}}{ "attribute": "name" }{{/helpers.highlight}}</a>
            <p>{{ description }}</p>
            <p>Price: \${{ price }}</p>
          </div>
        </div>
      `,
      empty: 'No results for <q>{{ query }}</q>',
    }
}),
ZA

the title

AB
A
instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: `
        <div>
          <div class="hit-name">
            <a href="{{url}}">{{#helpers.highlight}}{ "attribute": "name" }{{/helpers.highlight}}</a>
            <p>{{ description }}</p>
            <p>Price: \${{ price }}</p>
          </div>
        </div>
      `,
      empty: 'No results for <q>{{ query }}</q>',
    }
}),
ZA

the title
EOT;

        $this->assertSame($expected, StringUtilities::normalizeLineEndings(trim($this->renderString($template, ['title' => 'the title']))));
    }
}
