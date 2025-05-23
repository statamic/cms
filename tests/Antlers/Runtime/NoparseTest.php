<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class NoparseTest extends ParserTestCase
{
    use FakesViews;

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

    public function test_noparse_in_nested_partials_renders_correctly()
    {
        $template = <<<'EOT'
    {{ partial:partial_a }}
        {{ partial:partial_a }}
            {{ noparse }}inside noparse{{ /noparse }}
        {{ /partial:partial_a }}
    {{ /partial:partial_a }}

    {{ partial:partial_a }}
        {{ partial:partial_a }}
            {{ noparse }}inside noparse{{ /noparse }}
        {{ /partial:partial_a }}
    {{ /partial:partial_a }}

    {{ partial:partial_a }}
        {{ partial:partial_a }}
            {{ noparse }}inside noparse{{ /noparse }}
        {{ /partial:partial_a }}
    {{ /partial:partial_a }}
EOT;

        GlobalRuntimeState::$peekCallbacks[] = function ($processor, $nodes) {
            NodeProcessor::$break = true;
        };

        $this->withFakeViews();
        $this->viewShouldReturnRaw('partial_a', '{{ slot }}');

        $actual = StringUtilities::normalizeLineEndings(trim($this->renderString($template)));

        $occurrences = substr_count($actual, 'inside noparse');
        $this->assertEquals(3, $occurrences, "Expected 'inside noparse' to appear exactly 3 times");
    }
}
