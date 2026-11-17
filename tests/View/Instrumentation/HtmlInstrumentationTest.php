<?php

namespace Tests\View\Instrumentation;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\Antlers\TemplateAnalyzer;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\Antlers\ParserTestCase;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;

class HtmlInstrumentationTest extends ParserTestCase
{
    use DecodesMarkers;

    public function test_marker_expressions_keep_interpolations_as_authored()
    {
        $instrumenter = HtmlInstrumentation::make();

        $template = '<p>{{ if {partial:thing} | length > 5 }}long{{ /if }}</p>'
            .'<b>{{ title | ensure_right:{partial:thing} }}</b>'
            .'<i>{{ switch(({partial:thing} | length > 5) => "long", () => "short") }}</i>'
            .'<u>{{ x = "{partial:thing}" }}</u>';

        $this->assertSame([
            'if {partial:thing} | length > 5',
            'title | ensure_right:{partial:thing}',
            'switch(({partial:thing} | length > 5) => "long", () => "short")',
            'x = "{partial:thing}"',
        ], $this->markerExpressions($instrumenter($template)));

        $this->assertStringNotContainsString('int_', implode(' ', $this->markerExpressions($instrumenter($template))));
    }

    public function test_comments_instrument_single_nodes_in_normal_element_content()
    {
        $metadata = [
            'type' => 'single',
            'engine' => 'antlers',
            'expression' => 'title',
            'line' => 1,
            'start' => 3,
            'end' => 14,
            'context' => 'element-content',
            'element' => 'p',
            'view' => 'pages.home',
            'id' => 1,
        ];
        $encoded = base64_encode(json_encode($metadata));

        $instrumented = HtmlInstrumentation::make()
            ->comments('debug')
            ->withMetadata(['view' => 'pages.home'])
            ->instrument('<p>{{ title }}</p>');

        $this->assertSame(
            '<p><!-- debug:start '.$encoded.' -->{{ title }}<!-- debug:end 1 --></p>',
            $instrumented
        );
        $this->assertSame($metadata, HtmlInstrumentation::decode($instrumented));
    }

    public function test_pair_markers_remain_balanced_across_if_else_branches()
    {
        $instrumented = $this->plainCommentInstrumenter()->instrument(
            '<div>{{ if primary }}One{{ elseif secondary }}Two{{ else }}Three{{ /if }}</div>'
        );

        foreach ([[true, false], [false, true], [false, false]] as [$primary, $secondary]) {
            $rendered = $this->renderString($instrumented, compact('primary', 'secondary'));

            $this->assertSame(1, substr_count($rendered, '<!-- start:pair:if primary -->'));
            $this->assertSame(1, substr_count($rendered, '<!-- end:pair:if primary -->'));
        }
    }

    public function test_pairs_crossing_element_containers_are_not_given_dom_boundaries()
    {
        $template = '<section>{{ if show }}<div>{{ /if }}</section>';

        $this->assertSame($template, $this->plainCommentInstrumenter()->instrument($template));

        $sameNamedSiblings = '<div>{{ if show }}</div><div>{{ /if }}</div>';

        $this->assertSame($sameNamedSiblings, $this->plainCommentInstrumenter()->instrument($sameNamedSiblings));

        $contained = '<div>{{ if show }}<span>one</span>{{ /if }}</div>';

        $this->assertStringContainsString(
            '<!-- start:pair:if show -->',
            $this->plainCommentInstrumenter()->instrument($contained)
        );
    }

    public function test_dynamic_element_ancestry_blocks_comments_and_attribute_layers()
    {
        $instrumented = $this->plainCommentInstrumenter()
            ->attributes()
            ->instrument('<x-{{ kind }} class="{{ classes }}"><span data-id="{{ id }}">{{ content }}</span></x-{{ kind }}><p>{{ after }}</p>');

        $this->assertSame(1, substr_count($instrumented, '<!-- start:single:'));
        $this->assertStringContainsString(
            '<p data-antlers="',
            $instrumented
        );
        $this->assertSame(1, substr_count($instrumented, 'data-antlers='));
        $this->assertStringNotContainsString('data-antlers=', substr($instrumented, 0, strpos($instrumented, '<p')));

        $selfClosing = $this->plainCommentInstrumenter()
            ->attributes()
            ->instrument('<x-{{ kind }} /><p>{{ after }}</p>');

        $this->assertStringStartsWith('<x-{{ kind }} /><p data-antlers="', $selfClosing);
        $this->assertStringContainsString('<!-- start:single:after -->{{ after }}<!-- end:single:after --></p>', $selfClosing);
    }

    public function test_escaped_antlers_inside_an_element_name_is_spliced_after_the_full_name_run()
    {
        $instrumented = $this->plainCommentInstrumenter()
            ->attributes()
            ->instrument('<di@{{}}v class="{{ classes }}">{{ content }}</di@{{}}v><p>{{ after }}</p>');

        $this->assertStringContainsString('<di@{{}}v data-antlers="', $instrumented);
        $this->assertStringContainsString('class="{{ classes }}">', $instrumented);
        $this->assertSame(2, substr_count($instrumented, '<!-- start:single:'));
        $this->assertSame(2, substr_count($instrumented, 'data-antlers='));
        $this->assertStringContainsString('<p data-antlers="', $instrumented);
    }

    public function test_noparse_html_controls_marker_safety_but_is_not_itself_instrumented()
    {
        $instrumented = $this->plainCommentInstrumenter()->instrument(
            '@{{ escaped }}{{ noparse }}<title>literal{{ /noparse }}{{ real }}</title><p>{{ after }}</p>'
        );

        $this->assertSame(1, substr_count($instrumented, '<!-- start:single:'));
        $this->assertStringContainsString('{{ real }}</title>', $instrumented);
        $this->assertStringContainsString(
            '<p><!-- start:single:after -->{{ after }}<!-- end:single:after --></p>',
            $instrumented
        );
        $this->assertStringNotContainsString('single:noparse', $instrumented);
        $this->assertStringStartsWith('@{{ escaped }}{{ noparse }}', $instrumented);
    }

    public function test_unsafe_comment_contexts_are_left_alone_or_use_attributes()
    {
        $template = '<title>{{ title }}</title><pre><code>{{ code }}</code></pre><script>{{ script }}</script><p>{{ body }}</p>';

        $comments = HtmlInstrumentation::make()->instrument($template);

        $this->assertSame(1, substr_count($comments, '<!-- antlers:start '));
        $this->assertStringContainsString('<title>{{ title }}</title>', $comments);
        $this->assertStringContainsString('<code>{{ code }}</code>', $comments);
        $this->assertStringContainsString('<script>{{ script }}</script>', $comments);

        $attributes = HtmlInstrumentation::make()
            ->withoutComments()
            ->jsonAttribute('data-debug')
            ->instrument($template);

        $this->assertStringStartsWith('<title data-debug="', $attributes);
        $this->assertStringContainsString('<code data-debug="', $attributes);
        $this->assertStringContainsString('<script data-debug="', $attributes);
        $this->assertSame(4, substr_count($attributes, 'data-debug='));
        $this->assertStringNotContainsString('<!-- antlers:', $attributes);
    }

    public function test_attribute_layers_collect_eligible_nodes_on_the_owning_opening_element()
    {
        $instrumented = HtmlInstrumentation::make()
            ->withoutComments()
            ->attribute('data-antlers', function ($markers) {
                return implode('|', array_column($markers, 'expression'));
            })
            ->attribute('data-antlers-contexts', function ($markers) {
                return implode(',', array_column($markers, 'context'));
            })
            ->instrument('Hello <a href="{{ url }}" {{ attributes }}>{{ label }}</a>');

        $this->assertSame(
            'Hello <a data-antlers="url|attributes|label" data-antlers-contexts="attribute-value,tag-open,element-content" href="{{ url }}" {{ attributes }}>{{ label }}</a>',
            $instrumented
        );
    }

    public function test_attribute_layers_stay_aligned_around_escaped_antlers()
    {
        $instrumenter = fn () => HtmlInstrumentation::make()
            ->withoutComments()
            ->attribute('data-antlers', fn ($markers) => count($markers));

        $this->assertSame(
            '<div data-antlers="1" class="a">@{{ e }}{{ var }}</div>',
            $instrumenter()->instrument('<div class="a">@{{ e }}{{ var }}</div>')
        );

        $this->assertSame(
            '<noscript data-antlers="1">@{{ escaped }}{{ var }}</noscript>',
            $instrumenter()->instrument('<noscript>@{{ escaped }}{{ var }}</noscript>')
        );

        $this->assertSame(
            '@{{ e }}@{{ f }}<div><span data-antlers="1">{{ var }}</span></div>',
            $instrumenter()->instrument('@{{ e }}@{{ f }}<div><span>{{ var }}</span></div>')
        );
    }

    public function test_root_positions_in_whole_page_templates_are_not_given_comment_markers()
    {
        $beforeDoctype = '{{ early }}<!doctype html><html><body><p>{{ x }}</p></body></html>';
        $instrumented = HtmlInstrumentation::make()->comments('probe')->instrument($beforeDoctype);

        $this->assertStringStartsWith('{{ early }}<!doctype html>', $instrumented);
        $this->assertStringContainsString('<p><!-- probe:start ', $instrumented);

        $betweenHeadAndBody = '<html><head><title>t</title></head>{{ gap }}<body><p>{{ x }}</p></body></html>';
        $instrumented = HtmlInstrumentation::make()->comments('probe')->instrument($betweenHeadAndBody);

        $this->assertStringContainsString('</head>{{ gap }}<body>', $instrumented);
        $this->assertStringContainsString('<p><!-- probe:start ', $instrumented);

        $afterBody = '<html><body><p>{{ x }}</p></body></html>{{ late }}';
        $instrumented = HtmlInstrumentation::make()->comments('probe')->instrument($afterBody);

        $this->assertStringEndsWith('</html>{{ late }}', $instrumented);
        $this->assertStringContainsString('<p><!-- probe:start ', $instrumented);
    }

    public function test_table_foster_parenting_uses_attributes_instead_of_comment_boundaries()
    {
        $instrumented = HtmlInstrumentation::make()
            ->attributes('data-antlers')
            ->instrument('<div><table>{{ direct }}<tr><td>{{ cell }}</td></tr></table></div>');
        $tableStart = strpos($instrumented, '<table');
        $rowStart = strpos($instrumented, '<tr');

        $this->assertSame(1, substr_count($instrumented, '<!-- antlers:start '));
        $this->assertStringNotContainsString(
            '<!-- antlers:start ',
            substr($instrumented, $tableStart, $rowStart - $tableStart)
        );
        $this->assertStringContainsString('<div data-antlers="', $instrumented);
    }

    public function test_pending_formatting_reconstruction_uses_an_element_attribute()
    {
        $instrumented = HtmlInstrumentation::make()
            ->attributes('data-antlers')
            ->only('inside')
            ->instrument('<p><b>before<div>{{ inside }}</b>after</div>');

        $this->assertStringContainsString('<div data-antlers="', $instrumented);
        $this->assertStringNotContainsString('<!-- antlers:start ', $instrumented);
    }

    public function test_existing_attribute_layer_is_not_duplicated()
    {
        $instrumented = HtmlInstrumentation::make()
            ->withoutComments()
            ->attributes('data-antlers')
            ->instrument('<div data-antlers="app-owned" class="{{ classes }}">Content</div>');

        $this->assertSame(1, substr_count($instrumented, 'data-antlers='));
        $this->assertStringContainsString('data-antlers="app-owned"', $instrumented);

        $unclaimed = HtmlInstrumentation::make()
            ->withoutComments()
            ->attributes('data-antlers')
            ->instrument('<div class="{{ classes }}">Content</div>');

        $this->assertStringContainsString('<div data-antlers="', $unclaimed);
    }

    public function test_attribute_name_text_inside_other_values_does_not_block_instrumentation()
    {
        $unquoted = HtmlInstrumentation::make()
            ->withoutComments()
            ->attributes('data-antlers')
            ->instrument('<div title=x/data-antlers=app class="{{ classes }}">Content</div>');

        $this->assertStringStartsWith('<div data-antlers="', $unquoted);
        $this->assertSame(2, substr_count($unquoted, 'data-antlers='));

        $quoted = HtmlInstrumentation::make()
            ->withoutComments()
            ->attributes('data-antlers')
            ->instrument('<div title="mentions data-antlers= here" class="{{ classes }}">Content</div>');

        $this->assertStringStartsWith('<div data-antlers="', $quoted);
    }

    public function test_regions_ending_inside_quoted_strings_shrink_the_recovered_tag_boundary()
    {
        $template = '<div title="{{ value == "}}" ? "many > one" : "one" }}" data-antlers="app-owned" class="{{ classes }}">Content</div>';

        $instrumented = HtmlInstrumentation::make()
            ->withoutComments()
            ->attributes('data-antlers')
            ->instrument($template);

        $this->assertSame(2, substr_count($instrumented, 'data-antlers='));

        $parser = new DocumentParser();
        $parser->parse($instrumented);

        $this->assertSame($instrumented, $parser->html()->markDirty()->toHtml());
    }

    #[DataProvider('invalidConfigurationProvider')]
    public function test_invalid_prefixes_and_attribute_names_are_rejected_before_they_can_break_html($method, $argument)
    {
        $this->expectException(InvalidArgumentException::class);

        HtmlInstrumentation::make()->{$method}($argument);
    }

    public static function invalidConfigurationProvider()
    {
        return [
            'prefix closing the comment' => ['comments', 'debug -->'],
            'prefix with double hyphens' => ['comments', 'debug--trace'],
            'attribute name with a space' => ['attributes', 'data-bad name'],
        ];
    }

    public function test_crlf_templates_preserve_marker_positions_and_source_offsets()
    {
        $template = "<p>{{ first }}</p>\r\n<p>{{ second }}</p>";
        $instrumented = HtmlInstrumentation::make()->instrument($template);

        $this->assertStringContainsString("</p>\r\n<p><!-- antlers:start ", $instrumented);
        $this->assertStringContainsString('{{ second }}<!-- antlers:end 2 --></p>', $instrumented);

        preg_match_all('/antlers:start ([A-Za-z0-9+\/=]+) -->/', $instrumented, $matches);
        $second = HtmlInstrumentation::decode($matches[1][1]);

        $this->assertSame(23, $second['start']);
        $this->assertSame(35, $second['end']);
    }

    public function test_repeat_instrumentation_is_memoized_per_view_and_template()
    {
        $instrumenter = HtmlInstrumentation::make()->attributes('data-antlers');
        $template = '<p>{{ title }}</p>';
        $results = new ReflectionProperty(HtmlInstrumentation::class, 'results');
        $results->setValue(null, []);
        $memoize = fn () => $results->setValue(null, array_map(fn ($entry) => ['memoized', $entry[1], $entry[2]], $results->getValue()));

        $first = $instrumenter->instrument($template);

        $this->assertSame([$first], array_column(array_values($results->getValue()), 0));

        $memoize();

        $this->assertSame('memoized', $instrumenter->instrument($template));
        $this->assertCount(1, $results->getValue());

        $previous = GlobalRuntimeState::$currentExecutionFile;
        GlobalRuntimeState::$currentExecutionFile = 'partials/card.antlers.html';

        try {
            $viewScoped = $instrumenter->instrument($template);
        } finally {
            GlobalRuntimeState::$currentExecutionFile = $previous;
        }

        $this->assertNotSame($first, $viewScoped);
        $this->assertNotSame('memoized', $viewScoped);
        $this->assertSame('partials/card.antlers.html', HtmlInstrumentation::decode($viewScoped)['view']);
        $this->assertCount(2, $results->getValue());

        $instrumenter->comments('debug');
        $reconfigured = $instrumenter->instrument($template);

        $this->assertStringContainsString('<!-- debug:start ', $reconfigured);
        $this->assertNotSame($first, $reconfigured);
        $this->assertCount(3, $results->getValue());

        $memoize();

        $this->assertSame('memoized', $instrumenter->instrument($template));
    }

    public function test_marker_ids_correlate_pairs_and_attribute_payloads()
    {
        $instrumented = HtmlInstrumentation::make()
            ->attributes('data-antlers')
            ->instrument('<p>{{ if a }}x{{ /if }}{{ title }}</p><div class="{{ classes }}">y</div>');

        $this->assertSame(1, substr_count($instrumented, '<!-- antlers:end 1 -->'));
        $this->assertSame(1, substr_count($instrumented, '<!-- antlers:end 2 -->'));

        preg_match_all('/data-antlers="([^"]+)"/', $instrumented, $matches);
        $paragraphPayload = HtmlInstrumentation::decode($matches[1][0]);
        $divPayload = HtmlInstrumentation::decode($matches[1][1]);

        $this->assertSame([1, 2], array_column($paragraphPayload, 'id'));
        $this->assertSame(3, $divPayload[0]['id']);
        $this->assertSame('classes', $divPayload[0]['expression']);
    }

    public function test_marker_metadata_never_exposes_the_absolute_application_path()
    {
        $previous = GlobalRuntimeState::$currentExecutionFile;
        GlobalRuntimeState::$currentExecutionFile = base_path('resources/views/card.antlers.html');

        try {
            $instrumented = HtmlInstrumentation::make()->instrument('<p>{{ title }}</p>');
        } finally {
            GlobalRuntimeState::$currentExecutionFile = $previous;
        }

        $metadata = HtmlInstrumentation::decode($instrumented);

        $this->assertSame('resources/views/card.antlers.html', $metadata['view']);
        $this->assertStringNotContainsString(str_replace('\\', '/', base_path()), $metadata['view']);
    }

    public function test_an_unquoted_antlers_value_does_not_steal_the_following_attributes()
    {
        $source = '<div a={{ first }} b="{{ second }}" c={{ third }} d=prefix{{ fourth }} e="{{ fifth }}">';
        $analysis = (new TemplateAnalyzer)->analyze($source);
        $this->assertSame(['a', 'b', 'c', 'd', 'e'], array_map(fn ($region) => $region->context()->attributeName, $analysis['regions']));

        $output = HtmlInstrumentation::make()->withoutComments()->attributes('data-probe')->instrument($source);
        preg_match('/data-probe="([^"]+)"/', $output, $matches);
        $this->assertSame(['a', 'b', 'c', 'd', 'e'], array_column(HtmlInstrumentation::decode($matches[1]), 'attribute'));
        $this->assertStringContainsString('a={{ first }} b="{{ second }}" c={{ third }} d=prefix{{ fourth }} e="{{ fifth }}"', $output);
    }

    public function test_regions_inside_php_blocks_are_left_alone()
    {
        $template = '<p><?php if ($a > 1) { $b = "{{ x }}"; } ?></p><span>{{ y }}</span><?= {{ z }} ?>';

        $instrumented = HtmlInstrumentation::make()->attributes('data-antlers')->instrument($template);

        $this->assertStringStartsWith('<p><?php if ($a > 1) { $b = "{{ x }}"; } ?></p><span data-antlers="', $instrumented);
        $this->assertStringEndsWith('</span><?= {{ z }} ?>', $instrumented);
        $this->assertSame(1, substr_count($instrumented, '<!-- antlers:start '));
        $this->assertSame(
            ['x='.HtmlInstrumentation::SKIP_INSIDE_PHP, 'z='.HtmlInstrumentation::SKIP_INSIDE_PHP],
            $this->skipReasons($template)
        );

        $inTag = '<div <?php if ($a->b): ?>data-x="{{ x }}"<?php endif; ?> <?= $a > 1 ? "y" : "n" ?>><p>{{ y }}</p></div>';
        $instrumented = HtmlInstrumentation::make()->instrument($inTag);

        $this->assertStringStartsWith('<div <?php if ($a->b): ?>data-x="{{ x }}"<?php endif; ?> <?= $a > 1 ? "y" : "n" ?>><p><!-- antlers:start ', $instrumented);
        $this->assertSame(1, substr_count($instrumented, '<!-- antlers:start '));

        $inValue = '<div class="<?php echo $a ? "on" : "off" ?> {{ x }}" data-id="{{ id }}"><p>{{ y }}</p></div>';
        $instrumented = HtmlInstrumentation::make()->instrument($inValue);

        $this->assertStringStartsWith('<div class="<?php echo $a ? "on" : "off" ?> {{ x }}" data-id="{{ id }}"><p><!-- antlers:start ', $instrumented);
        $this->assertSame(1, substr_count($instrumented, '<!-- antlers:start '));
    }

    public function test_comment_markers_do_not_split_character_references()
    {
        $template = '<i class="icon">&#x{{ icon }};</i><span>&{{ entity }};</span><b>&{{ if named }}copy{{ else }}reg{{ /if }};</b><p>Tom &amp; {{ name }} &amp;{{ other }}</p><a href="?a&{{ query }}">x</a>';

        $this->assertSame(
            [
                'icon='.HtmlInstrumentation::SKIP_INSIDE_CHARACTER_REFERENCE,
                'entity='.HtmlInstrumentation::SKIP_INSIDE_CHARACTER_REFERENCE,
                'if named='.HtmlInstrumentation::SKIP_INSIDE_CHARACTER_REFERENCE,
                'query='.HtmlInstrumentation::SKIP_NO_STRATEGY,
            ],
            $this->skipReasons($template)
        );

        $instrumented = HtmlInstrumentation::make()->attributes('data-antlers')->instrument($template);

        $this->assertMatchesRegularExpression('/^<i data-antlers="[^"]+" class="icon">&#x\{\{ icon \}\};<\/i><span data-antlers="[^"]+">&\{\{ entity \}\};<\/span><b data-antlers="[^"]+">&\{\{ if named \}\}copy\{\{ else \}\}reg\{\{ \/if \}\};<\/b>/', $instrumented);
        $this->assertSame(2, substr_count($instrumented, '<!-- antlers:start '));
        $this->assertMatchesRegularExpression('/Tom &amp; <!-- antlers:start [^ ]+ -->\{\{ name \}\}<!-- antlers:end \d+ --> &amp;<!-- antlers:start [^ ]+ -->\{\{ other \}\}/', $instrumented);
    }

    public function test_antlers_php_nodes_are_left_alone_and_do_not_disturb_their_surroundings()
    {
        $template = '<section>{{? echo "<div class=\'x\'>"; ?}}{{ title }}</section>'
            .'<ul>{{? for ($i = 0; $i <count($items); $i++) { echo $i; } ?}}<li>{{ total }}</li></ul>'
            .'<input {{$ $field->attrs() $}} data-x="{{ x }}">'
            .'<script>{{? $end = "</script>"; ?}} var a = {{ value }};</script>'
            .'{{? $open = "<textarea>"; ?}}<p>{{ after }}</p>';

        $instrumented = HtmlInstrumentation::make()->attributes('data-antlers')->instrument($template);
        $layer = ' data-antlers="[^"]+"';

        $this->assertMatchesRegularExpression(
            '/^<section'.$layer.'>\{\{\? echo "<div class=\'x\'>"; \?\}\}<!-- antlers:start [^ ]+ -->\{\{ title \}\}<!-- antlers:end 1 --><\/section>'
            .'<ul>\{\{\? for \(\$i = 0; \$i <count\(\$items\); \$i\+\+\) \{ echo \$i; \} \?\}\}<li'.$layer.'><!-- antlers:start [^ ]+ -->\{\{ total \}\}<!-- antlers:end 2 --><\/li><\/ul>'
            .'<input'.$layer.' \{\{\$ \$field->attrs\(\) \$\}\} data-x="\{\{ x \}\}">'
            .'<script'.$layer.'>\{\{\? \$end = "<\/script>"; \?\}\} var a = \{\{ value \}\};<\/script>'
            .'\{\{\? \$open = "<textarea>"; \?\}\}<p'.$layer.'><!-- antlers:start [^ ]+ -->\{\{ after \}\}<!-- antlers:end 5 --><\/p>$/',
            $instrumented
        );

        $this->assertSame(
            ['x='.HtmlInstrumentation::SKIP_NO_STRATEGY, 'value='.HtmlInstrumentation::SKIP_NO_STRATEGY],
            $this->skipReasons($template)
        );
    }

    #[DataProvider('hostileMarkupProvider')]
    public function test_misplaced_marker_removal_is_pinned_for_hostile_markup($input, $expected)
    {
        $instrumenter = HtmlInstrumentation::make()->comments('antlers')->attributes('data-antlers');
        $input = $this->decodeMarkerPlaceholders($input);
        $expected = $expected === null ? $input : $this->decodeMarkerPlaceholders($expected);
        $cleaned = HtmlInstrumentation::removeMisplacedMarkers($input, [$instrumenter]);

        $this->assertSame($expected, $cleaned);
        $this->assertSame($cleaned, HtmlInstrumentation::removeMisplacedMarkers($cleaned, [$instrumenter]));
    }

    public static function hostileMarkupProvider()
    {
        return [
            'content, attributes, raw text and comments in one document' => [
                '<main[L]>[S]A[E]<!-- authored --><input[L] value="<b[L]>[S]B[E]</b>" title=\'a > b\'><title>[S]C[E]</title><TEXTAREA[L]><b[L]>[S]D[E]</b></TEXTAREA><p[L]>[S]1 < 2[E]</p></main>',
                '<main[L]>[S]A[E]<!-- authored --><input[L] value="<b>B</b>" title=\'a > b\'><title>C</title><TEXTAREA[L]><b>D</b></TEXTAREA><p[L]>[S]1 < 2[E]</p></main>',
            ],
            'apostrophe in an unquoted value' => ['<p data-note=it\'s>[S]A[E]</p>', null],
            'stray quote after a quoted value' => ['<p class="a"">[S]A[E]</p>', null],
            'spaces around the equals sign' => ['<a title = "x [S]A[E]" data-x=\'"\'>[S]B[E]</a>', '<a title = "x A" data-x=\'"\'>[S]B[E]</a>'],
            'markup inside an authored comment' => ['<!-- old: <aside[L]>[S]A[E]</aside> --><footer>[S]B[E]</footer>', '<!-- old: <aside>A</aside> --><footer>[S]B[E]</footer>'],
            'conditional comment' => ['<!--[if lt IE 9]>[S]A[E]<![endif]--><!--><p>[S]B[E]</p>', '<!--[if lt IE 9]>A<![endif]--><!--><p>[S]B[E]</p>'],
            'title attr with comment' => ['<div title="<!-- x -->">[S]A[E]</div>', null],
            'title attr with comment + marker inside' => ['<div title="<!-- x [S]A[E] -->">[S]B[E]</div>', '<div title="<!-- x A -->">[S]B[E]</div>'],
            'script string with <!--' => ['<script>var s = "<!--";</script>[S]A[E]', null],
            'script string with <!-- and marker in script' => ['<script>var s = "<!--"; [S]A[E]</script><p>[S]B[E]</p>', '<script>var s = "<!--"; A</script><p>[S]B[E]</p>'],
            'single-quoted attr containing dq' => ['<div data-a=\'x"y\'>[S]A[E]</div>', null],
            'single-quoted attr containing dq + marker' => ['<div data-a=\'x"y [S]A[E]\'>[S]B[E]</div>', '<div data-a=\'x"y A\'>[S]B[E]</div>'],
            'unquoted with dq' => ['<div a=b"c>[S]A[E]</div>', null],
            'unquoted with dq + marker' => ['<div a=b"c[S]A[E]>[S]B[E]</div>', '<div a=b"cA>[S]B[E]</div>'],
            'unquoted containing <!--' => ['<div a=<!--x>[S]A[E]</div>', null],
            'unquoted containing <!-- then marker' => ['<div a=<!--x [S]A[E]>[S]B[E]</div>', '<div a=<!--x A>[S]B[E]</div>'],
            'empty comment <!-->' => ['<!-->[S]A[E]', null],
            'empty comment <!--->' => ['<!--->[S]A[E]', null],
            'comment with -- inside' => ['<!-- -- -->[S]A[E]', null],
            'comment with --!>' => ['<!-- x --!>[S]A[E]', null],
            'script end with space' => ['<script>[S]A[E]</script ><p>[S]B[E]</p>', '<script>A</script ><p>[S]B[E]</p>'],
            'script end upper' => ['<script>[S]A[E]</SCRIPT><p>[S]B[E]</p>', '<script>A</SCRIPT><p>[S]B[E]</p>'],
            'script end tab' => ['<script>[S]A[E]</script[T]><p>[S]B[E]</p>', '<script>A</script[T]><p>[S]B[E]</p>'],
            'style in svg' => ['<svg><style>[S]A[E]</style><text>[S]B[E]</text></svg>', '<svg><style>A</style><text>[S]B[E]</text></svg>'],
            'svg script' => ['<svg><script>[S]A[E]</script></svg><p>[S]B[E]</p>', '<svg><script>A</script></svg><p>[S]B[E]</p>'],
            'uppercase tags' => ['<TITLE>[S]A[E]</TITLE><P>[S]B[E]</P>', '<TITLE>A</TITLE><P>[S]B[E]</P>'],
            'textarea in svg' => ['<svg><textarea>[S]A[E]</textarea></svg>', '<svg><textarea>A</textarea></svg>'],
            'pre' => ['<pre>[S]A[E]</pre>', null],
            'plaintext' => ['<plaintext>[S]A[E]</plaintext><p>[S]B[E]</p>', '<plaintext>A</plaintext><p>B</p>'],
            'noscript' => ['<noscript>[S]A[E]</noscript><p>[S]B[E]</p>', '<noscript>A</noscript><p>[S]B[E]</p>'],
            'template' => ['<template>[S]A[E]</template>', null],
            'svg title (not rcdata)' => ['<svg><title>[S]A[E]</title></svg>', '<svg><title>A</title></svg>'],
            'iframe noembed xmp noframes' => ['<iframe>[S]A[E]</iframe><noembed>[S]B[E]</noembed><xmp>[S]C[E]</xmp><noframes>[S]D[E]</noframes><p>[S]E[E]</p>', '<iframe>A</iframe><noembed>B</noembed><xmp>C</xmp><noframes>D</noframes><p>[S]E[E]</p>'],
            'script double-escape' => ['<script><!--<script>[S]A[E]</script>[S]B[E]--></script><p>[S]C[E]</p>', '<script><!--<script>A</script>[S]B[E]--></script><p>[S]C[E]</p>'],
            'attribute layer in attr value' => ['<div title="x[L] y" class="a">[S]A[E]</div>', '<div title="x y" class="a">[S]A[E]</div>'],
            'attribute layer in single-quoted attr value' => ['<div title=\'x[L] y\'>[S]A[E]</div>', '<div title=\'x y\'>[S]A[E]</div>'],
            'attribute layer legit' => ['<div[L] class="a">[S]A[E]</div>', null],
            'marker right after <!--' => ['<!--[E] rest --><p>[S]A[E]</p>', '<!-- rest --><p>[S]A[E]</p>'],
            'marker between attrs' => ['<div class="a" [S]A[E] id="b">x</div>', '<div class="a" A id="b">x</div>'],
            'marker in tag before >' => ['<div class="a"[E]>x</div>', '<div class="a">x</div>'],
            'marker in closing tag' => ['<div>x</div[E]>', null],
            'tag name adjacent marker' => ['<div[S]>x</div>', null],
            'non-marker text <3' => ['<3 [S]A[E]', null],
            'php tag' => ['<?php echo "<!-- x"; ?>[S]A[E]', null],
            'bogus comment <!x' => ['<!x [S]A[E]><p>[S]B[E]</p>', '<!x A><p>[S]B[E]</p>'],
            'bogus comment <?x' => ['<?x [S]A[E]><p>[S]B[E]</p>', '<?x A><p>[S]B[E]</p>'],
            'doctype' => ['<!DOCTYPE html [S]A[E]><p>[S]B[E]</p>', '<!DOCTYPE html A><p>[S]B[E]</p>'],
            'xml declaration' => ['<?xml version="1.0" [S]A[E]?><p>[S]B[E]</p>', '<?xml version="1.0" A?><p>[S]B[E]</p>'],
            'cdata' => ['<svg><![CDATA[ > [S]A[E] ]]><text>[S]B[E]</text></svg>', '<svg><![CDATA[ > A ]]><text>[S]B[E]</text></svg>'],
            'bogus closing tag' => ['</ x [S]A[E]><p>[S]B[E]</p>', '</ x A><p>[S]B[E]</p>'],
            'empty closing tag' => ['</>[S]A[E]', null],
            'closing slash at end' => ['<p>[S]A[E]</p></', null],
            'char ref' => ['<p>&am[S]p;[E]</p>', null],
            'marker inside attr then text' => ['<a href="/x[S]">[S]A[E]</a>', '<a href="/x">[S]A[E]</a>'],
            'unterminated tag' => ['<div class="a[S]A[E]', '<div class="aA'],
            'end marker without id in attr' => ['<div title="<!-- antlers:end -->">x</div>', '<div title="">x</div>'],
            'textarea case with attr' => ['<TextArea rows=3>[S]A[E]</TEXTAREA><p>[S]B[E]</p>', '<TextArea rows=3>A</TEXTAREA><p>[S]B[E]</p>'],
            'title inside svg then html title' => ['<svg><title>[S]A[E]</title></svg><title>[S]B[E]</title>', '<svg><title>A</title></svg><title>B</title>'],
            'script with </script inside string then real end' => ['<script>var a = "</scriptx>"; [S]A[E]</script><p>[S]B[E]</p>', null],
        ];
    }

    #[DataProvider('hostileMarkupAcrossConfigurationsProvider')]
    public function test_misplaced_marker_removal_is_pinned_across_configurations($configuration, $input, $expected)
    {
        $instrumenters = match ($configuration) {
            'comments+attrs' => [HtmlInstrumentation::make()->comments('antlers')->attributes('data-antlers')],
            'comments' => [HtmlInstrumentation::make()->comments('antlers')],
            'attrs' => [HtmlInstrumentation::make()->withoutComments()->attributes('data-antlers')],
            'two instrumenters' => [
                HtmlInstrumentation::make()->comments('trace')->attributes('data-trace'),
                HtmlInstrumentation::make()->comments('antlers')->attributes('data-antlers')->attribute('data-antlers-contexts', fn () => 'x'),
            ],
            'factory+attrs' => [HtmlInstrumentation::make()->markersUsing(fn () => ['<!--s-->', '<!--e-->'])->attributes('data-antlers')],
        };

        $input = $this->decodeMarkerPlaceholders($input);
        $expected = $expected === null ? $input : $this->decodeMarkerPlaceholders($expected);
        $cleaned = HtmlInstrumentation::removeMisplacedMarkers($input, $instrumenters);

        $this->assertSame($expected, $cleaned);
        $this->assertSame($cleaned, HtmlInstrumentation::removeMisplacedMarkers($cleaned, $instrumenters));
    }

    public static function hostileMarkupAcrossConfigurationsProvider()
    {
        return [
            'both prefixes and layers are stripped inside markup' => ['two instrumenters', '<div[TL][C] title="[TE] x[L]">[TS]A[TE][S]B[E]<script>[S]c[E][TE]</script><!-- note [TS][C] --></div>', '<div[TL][C] title=" x">[TS]A[TE][S]B[E]<script>c</script><!-- note  --></div>'],
            'a layer whose name extends another is matched on its own' => ['two instrumenters', '<div title="x[C] y" data-x=\'z[C]\'>[S]A[E]</div>', '<div title="x y" data-x=\'z\'>[S]A[E]</div>'],
            'custom comment factories leave comment markers alone' => ['factory+attrs', '<div title="[S] x[L]">[S]A[E]</div><!-- [S] -->', '<div title="[S] x">[S]A[E]</div><!-- [S] -->'],
            'attribute layers only' => ['attrs', '<p title="a[L]"[L]>[S]x[E]</p><input value=\'q[L]\'><script>[L]</script>', '<p title="a"[L]>[S]x[E]</p><input value=\'q\'><script></script>'],
            'comment markers only leave attribute layers alone' => ['comments', '<p title="a[L] [E]"[L]>[S]x[E]</p>', '<p title="a[L] "[L]>[S]x[E]</p>'],
            'a high byte after a less-than sign is text' => ['comments+attrs', '<é title="[E]">[S]x[E]</é>', null],
            'unterminated comments swallow the rest of the document' => ['comments+attrs', '<!-- open [S]A[E] <p>[S]B[E]', '<!-- open A <p>B'],
            'raw text elements clean their tag and their content' => ['comments+attrs', '<script title="[E]">var a = 1; [S]x[E]</script>[S]y[E]<textarea[L]>[S]z[E]</TEXTAREA>', '<script title="">var a = 1; x</script>[S]y[E]<textarea[L]>z</TEXTAREA>'],
            'closing tags are scanned for misplaced markers too' => ['comments+attrs', '<div>x</div title="[E]" [E]>[S]y[E]', '<div>x</div title="" >[S]y[E]'],
            'crlf line endings inside tags' => ['comments+attrs', '<div[CRLF]class="a"[CRLF][E]>[CRLF][S]x[E][CRLF]</div>', '<div[CRLF]class="a"[CRLF]>[CRLF][S]x[E][CRLF]</div>'],
            'single quotes may contain double quotes' => ['comments+attrs', '<a title=\'say "hi" [L] [E]\'>[S]x[E]</a>', '<a title=\'say "hi"  \'>[S]x[E]</a>'],
            'markers inside php blocks are treated as text' => ['comments+attrs', '<?php echo "<a title=\\"[E]\\">"; ?>[S]A[E]', '<?php echo "<a title=\\"\\">"; ?>[S]A[E]'],
            'lone less-than signs are text' => ['comments+attrs', '< [E] <3[E] <? [E]', '< [E] <3[E] <? '],
            'bogus comments swallow markers up to their end' => ['comments+attrs', '</ [E] <3[E] <![E] <!-[E]', '</  <3 <! <!-'],
            'unquoted values end at whitespace' => ['comments+attrs', '<div a=b[E]c d="e"[E]>[S]x[E]</div>', '<div a=bc d="e">[S]x[E]</div>'],
            'empty and odd comments' => ['comments+attrs', '<!-->[E]<!--->[E]<!---->[E]<!-- -- [E] -->[E]<!--[E]--!>[E]-->[E]', '<!-->[E]<!--->[E]<!---->[E]<!-- --  -->[E]<!----!>[E]-->[E]'],
            'a raw text end tag inside a string ends the element' => ['comments+attrs', '<script>var a = "</script>"; [S]x[E]</script>[S]y[E]', null],
            'markers adjacent to the tag name are kept' => ['comments+attrs', '<div[E] class="a"[E]>[S]x[E]</div[E]>', null],
            'a double quoted value ending in a layer name swallows the closing quote' => ['comments+attrs', '<div title="x[L] class="a">[S]x[E]</div>', '<div title="x class="a">[S]x[E]</div>'],
        ];
    }

    private function decodeMarkerPlaceholders(string $value): string
    {
        return str_replace(
            ['[S]', '[E]', '[L]', '[TS]', '[TE]', '[TL]', '[C]', '[T]', '[CRLF]'],
            [
                '<!-- antlers:start '.base64_encode('{"id":1}').' -->',
                '<!-- antlers:end 1 -->',
                ' data-antlers="W10="',
                '<!-- trace:start YQ== -->',
                '<!-- trace:end 2 -->',
                ' data-trace="W10="',
                ' data-antlers-contexts="eA=="',
                "\t",
                "\r\n",
            ],
            $value
        );
    }

    public function test_already_instrumented_templates_are_left_alone()
    {
        $instrumenter = HtmlInstrumentation::make();
        $instrumented = $instrumenter->instrument('<p>{{ title }}</p>');

        $this->assertSame('{{ wrapper }}'.$instrumented.'{{ /wrapper }}', $instrumenter->instrument('{{ wrapper }}'.$instrumented.'{{ /wrapper }}'));
    }

    public function test_skipped_regions_report_why_they_could_not_be_instrumented()
    {
        $this->assertSame(
            ['if show='.HtmlInstrumentation::SKIP_CROSS_CONTAINER_PAIR],
            $this->skipReasons('<section>{{ if show }}<div>{{ /if }}</section>')
        );

        $this->assertSame(
            ['if a='.HtmlInstrumentation::SKIP_NO_STRATEGY],
            $this->skipReasons('<script>{{ if a }}x{{ /if }}</script>')
        );

        $this->assertSame([
            'commented='.HtmlInstrumentation::SKIP_INSIDE_HTML_COMMENT,
            'typed='.HtmlInstrumentation::SKIP_INSIDE_DOCTYPE,
            'kind='.HtmlInstrumentation::SKIP_DYNAMIC_MARKUP,
            'inside='.HtmlInstrumentation::SKIP_DYNAMIC_MARKUP,
            'kind='.HtmlInstrumentation::SKIP_DYNAMIC_MARKUP,
        ], $this->skipReasons('<!-- {{ commented }} --><!doctype {{ typed }}><x-{{ kind }}>{{ inside }}</x-{{ kind }}>'));

        $this->assertSame([], $this->skipReasons('<p>{{ title }}</p>'));
    }

    public function test_regions_inside_recovered_end_tags_are_left_alone()
    {
        $reasons = [];
        $template = '<p>a</br {{ x }}>b<b>c</b {{ y }}></p>';

        $instrumented = HtmlInstrumentation::make()
            ->attributes('data-antlers')
            ->onSkip(function ($metadata, $region, $reason) use (&$reasons) {
                $reasons[] = $metadata['expression'].'='.$reason;
            })
            ->instrument($template);

        $this->assertSame($template, $instrumented);
        $this->assertSame(
            ['x='.HtmlInstrumentation::SKIP_CLOSING_TAG_MARKUP, 'y='.HtmlInstrumentation::SKIP_CLOSING_TAG_MARKUP],
            $reasons
        );
    }

    public function test_skip_reporting_instrumenters_bypass_the_result_cache()
    {
        $reasons = [];
        $collect = function ($metadata, $region, $reason) use (&$reasons) {
            $reasons[] = $metadata['expression'].'='.$reason;
        };
        $template = '<title>{{ blocked }}</title><p>{{ ok }}</p>';
        $expected = 'blocked='.HtmlInstrumentation::SKIP_NO_STRATEGY;
        $results = new ReflectionProperty(HtmlInstrumentation::class, 'results');
        $results->setValue(null, []);
        $memoize = fn () => $results->setValue(null, array_map(fn ($entry) => ['memoized', $entry[1], $entry[2]], $results->getValue()));

        $instrumenter = HtmlInstrumentation::make()->onSkip($collect);
        $first = $instrumenter->instrument($template);

        $this->assertCount(1, $results->getValue());

        $memoize();

        $this->assertSame($first, $instrumenter->instrument($template));
        $this->assertSame([$expected, $expected], $reasons);

        $reasons = [];
        $results->setValue(null, []);
        $instrumenters = [HtmlInstrumentation::make()->comments('other'), HtmlInstrumentation::make()->onSkip($collect)];
        $first = HtmlInstrumentation::instrumentTogether($template, $instrumenters);

        $this->assertCount(1, $results->getValue());

        $memoize();

        $this->assertSame($first, HtmlInstrumentation::instrumentTogether($template, $instrumenters));
        $this->assertSame([$expected, $expected], $reasons);
    }

    private function skipReasons($template)
    {
        $reasons = [];

        $instrumented = HtmlInstrumentation::make()
            ->onSkip(function ($metadata, $region, $reason) use (&$reasons) {
                $reasons[] = $metadata['expression'].'='.$reason;
            })
            ->instrument($template);

        if ($reasons === []) {
            $this->assertStringContainsString('<!-- antlers:start ', $instrumented);
        }

        return $reasons;
    }

    private function plainCommentInstrumenter()
    {
        return HtmlInstrumentation::make()->markersUsing(function ($metadata) {
            $label = $metadata['type'].':'.$metadata['expression'];

            return ['<!-- start:'.$label.' -->', '<!-- end:'.$label.' -->'];
        });
    }
}
