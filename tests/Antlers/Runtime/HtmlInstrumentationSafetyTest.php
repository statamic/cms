<?php

namespace Tests\Antlers\Runtime;

use DOMDocument;
use Statamic\Facades\Antlers;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Cascade;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationState;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Throwable;

class HtmlInstrumentationSafetyTest extends ParserTestCase
{
    use DecodesMarkers, FakesViews, InteractsWithRuntimeConfigurations;

    public function test_nested_views_inherit_unsafe_context_even_when_the_safe_result_is_cached()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('safety-inner', '<em>{{ title }}</em>');
        $this->viewShouldReturnRaw('safety-outer', '{{ partial:safety-inner }}');
        $data = ['title' => 'Hello'];
        $parser = $this->parser($data, true);
        $parser->setRuntimeConfiguration($this->runtimeConfiguration());

        Runtime::register(HtmlInstrumentation::make()->comments('first'))
            ->register(HtmlInstrumentation::make()->comments('second')->attributes('data-second'));

        $safe = '<section>{{ partial:safety-outer }}</section>';
        $safeOutput = (string) $parser->parse($safe, $data);
        $this->assertStringContainsString('<em data-second=', $safeOutput);
        $this->assertStringContainsString('<!-- first:start', $safeOutput);
        $this->assertStringContainsString('<!-- second:start', $safeOutput);

        foreach (['partial', 'include'] as $tag) {
            foreach (['textarea', 'script', 'attribute'] as $context) {
                $expression = '{{ '.$tag.':safety-outer title="Hello" }}';
                $source = match ($context) {
                    'textarea' => '<textarea>'.$expression.'</textarea>',
                    'script' => '<script>'.$expression.'</script>',
                    'attribute' => '<input value="'.$expression.'">',
                };

                for ($render = 0; $render < 2; $render++) {
                    $output = (string) $parser->parse($source, $data);
                    if ($context === 'attribute') {
                        $dom = new DOMDocument;
                        $dom->loadHTML($output, LIBXML_NOERROR | LIBXML_NOWARNING);
                        $value = $dom->getElementsByTagName('input')->item(0)->getAttribute('value');
                    } else {
                        preg_match('~^<'.$context.'\b[^>]*>(.*)</'.$context.'>$~s', $output, $matches);
                        $value = $matches[1] ?? null;
                    }

                    $this->assertSame('<em>Hello</em>', $value, $output);
                    $this->assertFalse(InstrumentationState::suppressed());
                }
            }
        }

        $this->assertSame($safeOutput, (string) $parser->parse($safe, $data));
    }

    public function test_tag_pair_bodies_are_instrumented_once_with_authored_offsets()
    {
        $this->app['statamic.tags']['echoing_pair'] = EchoingPairTag::class;
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data, HtmlInstrumentation::make()->attributes('data-antlers'));

        $source = '<ul>{{ echoing_pair }}<li class="{{ kind }}">{{ title }}</li>{{ /echoing_pair }}</ul>';
        RuntimeParser::clearRenderNodeCache();
        $output = (string) $parser->parse($source, $data);
        $cacheSize = count(RuntimeParser::getNodeCache());

        $markers = collect($this->decodedMarkers($output))->keyBy('expression');

        $this->assertSame(['echoing_pair', 'title'], $markers->keys()->all());
        $this->assertSame(mb_strpos($source, '{{ title }}'), $markers['title']['start']);
        $this->assertSame(1, substr_count($output, 'Hello'));
        $this->assertMatchesRegularExpression('/<li data-antlers="[^"]+" class="item">/', $output);
        preg_match('/<li data-antlers="([^"]+)"/', $output, $payload);
        $this->assertSame(['kind', 'title'], array_column(HtmlInstrumentation::decode($payload[1]), 'expression'));
        $this->assertSame($output, (string) $parser->parse($source, $data));
        $this->assertSame($cacheSize, count(RuntimeParser::getNodeCache()));
    }

    public function test_views_rendered_inside_parsed_tag_pairs_are_instrumented_once()
    {
        $this->app['statamic.tags']['echoing_pair'] = EchoingPairTag::class;
        $this->withFakeViews();
        $this->viewShouldReturnRaw('safety-inner', '<em>{{ title }}</em>');
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data);

        $sources = [
            '<div>{{ echoing_pair }}<b>{{ kind }}</b>{{ partial:safety-inner }}{{ /echoing_pair }}</div>' => 1,
            '<div>{{ echoing_pair:loop }}<b>{{ kind }}</b>{{ partial:safety-inner }}{{ /echoing_pair:loop }}</div>' => 2,
        ];

        foreach ($sources as $source => $iterations) {
            $output = (string) $parser->parse($source, $data);

            $markers = collect($this->decodedMarkers($output));

            $this->assertSame($iterations, $markers->where('expression', 'kind')->count(), $output);
            $this->assertSame($iterations, $markers->where('expression', 'title')->count(), $output);
            $this->assertSame(['safety-inner.antlers.html'], $markers->where('expression', 'title')->pluck('view')->unique()->all());
            $this->assertSame($iterations, substr_count($output, '<em>'));
        }
    }

    public function test_tag_bodies_rendered_after_the_tag_call_keep_their_authored_offsets()
    {
        $this->app['statamic.tags']['echoing_pair'] = EchoingPairTag::class;
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data);

        $sources = [
            '<ul>{{ echoing_pair:rows scope="row" }}<li>{{ title }}</li>{{ /echoing_pair:rows }}</ul>',
            '<ul>{{ echoing_pair:rows as="rows" }}{{ rows }}<li>{{ title }}</li>{{ /rows }}{{ /echoing_pair:rows }}</ul>',
            '<ul>{{ echoing_pair:none }}{{ if no_results }}<li>{{ title }}</li>{{ /if }}{{ /echoing_pair:none }}</ul>',
        ];

        foreach ($sources as $source) {
            RuntimeParser::clearRenderNodeCache();

            $output = (string) $parser->parse($source, $data);
            $markers = collect($this->decodedMarkers($output))->where('expression', 'title')->values();

            $this->assertCount(1, $markers, $output);
            $this->assertSame(mb_strpos($source, '{{ title }}'), $markers[0]['start'], $source);
            $this->assertSame(1, substr_count($output, 'Hello'), $output);
        }
    }

    public function test_tag_bodies_rendered_after_the_tag_call_keep_the_unsafe_context()
    {
        $this->app['statamic.tags']['echoing_pair'] = EchoingPairTag::class;
        $this->withFakeViews();
        $this->viewShouldReturnRaw('safety-inner', '<em>{{ title }}</em>');
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data);

        $bodies = [
            '{{ echoing_pair:trimmed_loop }} {{ partial:safety-inner }} {{ /echoing_pair:trimmed_loop }}' => '<em>Hello</em><em>Hello</em>',
            '{{ echoing_pair:rows scope="row" }}{{ partial:safety-inner }}{{ /echoing_pair:rows }}' => '<em>Hello</em>',
            '{{ echoing_pair:none }}{{ if no_results }}{{ partial:safety-inner }}{{ /if }}{{ /echoing_pair:none }}' => '<em>Hello</em>',
        ];

        foreach ($bodies as $body => $expected) {
            RuntimeParser::clearRenderNodeCache();

            $this->assertSame('<title>'.$expected.'</title>', (string) $parser->parse('<title>'.$body.'</title>', $data));
            $this->assertSame('<input value="'.$expected.'">', (string) $parser->parse('<input value="'.$body.'">', $data));
            $this->assertStringContainsString('<em><!-- antlers:start ', (string) $parser->parse('<p>'.$body.'</p>', $data));
            $this->assertFalse(InstrumentationState::suppressed());
        }
    }

    public function test_the_same_tag_body_is_instrumented_where_it_is_safe_after_rendering_where_it_is_not()
    {
        $this->app['statamic.tags']['echoing_pair'] = EchoingPairTag::class;
        $this->withFakeViews();
        $this->viewShouldReturnRaw('safety-inner', '<em>{{ title }}</em>');
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data);
        RuntimeParser::clearRenderNodeCache();

        $pair = '{{ echoing_pair }}{{ partial:safety-inner }}{{ /echoing_pair }}';
        $output = (string) $parser->parse('<textarea>'.$pair.'</textarea>'.$pair, $data);

        $this->assertStringStartsWith('<textarea><em>Hello</em></textarea><em><!-- antlers:start ', $output);
    }

    public function test_markers_are_removed_from_output_that_lands_in_an_unsafe_context()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('safety-field', '<textarea>{{ slot }}</textarea><input value="{{ slot }}"><p>{{ slot }}</p>');
        $data = ['title' => 'Hello', 'template' => '<b>{{ title }}</b>'];
        $parser = $this->instrumentedParser($data, HtmlInstrumentation::make()->attributes('data-antlers'))->cascade(app(Cascade::class));

        $slot = (string) $parser->parse('<div>{{ partial:safety-field }}<b>{{ title }}</b>{{ /partial:safety-field }}</div>', $data);

        $this->assertMatchesRegularExpression('/<textarea data-antlers="[^"]+"><b>Hello<\/b><\/textarea><input data-antlers="[^"]+" value="<b>Hello<\/b>">/', $slot);
        $this->assertMatchesRegularExpression('/<p[^>]*><!-- antlers:start [^ ]+ --><b data-antlers="[^"]+"><!-- antlers:start /', $slot);

        $yielded = (string) $parser->parse(
            '<title>{{ yield:seo }}</title><meta content="{{ yield:summary }}"><main>{{ section:seo }}{{ title }}{{ /section:seo }}{{ section:summary }}{{ title }}{{ /section:summary }}{{ title }}</main>',
            $data
        );

        $this->assertMatchesRegularExpression('/^<title[^>]*>Hello<\/title><meta[^>]* content="Hello"><main/', $yielded);
        $this->assertStringContainsString('-->Hello<!-- antlers:end ', $yielded);

        $this->assertMatchesRegularExpression(
            '/^<input data-antlers="[^"]+" value="<b>Hello<\/b>">$/',
            (string) $parser->parse('<input value="{{ template | antlers }}">', $data)
        );
    }

    public function test_tag_bodies_parsed_outside_the_tag_helpers_are_instrumented_once()
    {
        $this->app['statamic.tags']['echoing_pair'] = EchoingPairTag::class;
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data);

        $output = (string) $parser->parse('<ul>{{ echoing_pair:facade }}<li>{{ title }}</li>{{ /echoing_pair:facade }}</ul>', $data);

        $this->assertSame(2, substr_count($output, '<!-- antlers:start '));
        $this->assertSame(1, substr_count($output, '-->Hello<!-- antlers:end '));

        $cast = (string) $parser->parse('<ul>{{ echoing_pair:facade_string }}<li>{{ title }}</li>{{ /echoing_pair:facade_string }}</ul>', $data);

        $this->assertSame(1, substr_count($cast, '<!-- antlers:start '));
        $this->assertStringContainsString('<li>Hello</li>', $cast);
    }

    public function test_a_failing_nested_view_releases_instrumentation_suppression()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('safety-failure', '{{ title | nonexistent_safety_modifier }}');
        $data = ['title' => 'Hello'];
        $parser = $this->instrumentedParser($data);

        try {
            $parser->parse('<textarea>{{ partial:safety-failure }}</textarea>', $data);
            $this->fail('Expected the partial to throw.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('nonexistent_safety_modifier', $exception->getMessage());
        }

        $this->assertFalse(InstrumentationState::suppressed());
        $this->assertStringContainsString('<!-- antlers:start', (string) $parser->parse('<p>{{ title }}</p>', $data));
    }

    public function test_late_tracers_can_query_cached_nodes_after_other_templates_have_rendered()
    {
        RuntimeParser::clearRenderNodeCache();
        $parser = $this->parser([], true);
        $configuration = $this->runtimeConfiguration();
        $parser->setRuntimeConfiguration($configuration);
        $first = '<p>{{ title }}</p>';
        $second = '<aside>{{ title }}</aside>';
        $parser->parse($first, ['title' => 'one']);
        $parser->parse($second, ['title' => 'two']);

        $tracer = new class implements TracerContract
        {
            public array $contexts = [];

            public function onEnter(Span $span): mixed
            {
                if ($span->raw() instanceof AntlersNode) {
                    $node = $span->raw();
                    $this->contexts[] = [$node->htmlContext()?->elementName, $node->parentElement()?->name()];
                }

                return null;
            }

            public function onExit(Span $span, mixed $handle, mixed $output): void
            {
            }

            public function onRenderComplete(): void
            {
            }
        };

        Runtime::register($tracer);
        $parser->parse($first, ['title' => 'three']);
        $parser->parse($second, ['title' => 'four']);
        $parser->parse($first, ['title' => 'five']);

        $this->assertSame([['p', 'p'], ['aside', 'aside'], ['p', 'p']], $tracer->contexts);
        $this->assertFalse($configuration->annotateHtmlContext);
    }
}

class EchoingPairTag extends Tags
{
    public function index()
    {
        return $this->parse(['kind' => 'item']);
    }

    public function loop()
    {
        return $this->parseLoop([['kind' => 'first'], ['kind' => 'second']]);
    }

    public function trimmedLoop()
    {
        $this->content = trim($this->content);

        return $this->loop();
    }

    public function rows()
    {
        return [['kind' => 'row']];
    }

    public function none()
    {
        return [];
    }

    public function facade()
    {
        return Antlers::parse($this->content, $this->context->all());
    }

    public function facadeString()
    {
        return (string) Antlers::parse($this->content, $this->context->all());
    }
}
