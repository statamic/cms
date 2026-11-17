<?php

namespace Tests\Antlers\Runtime;

use ReflectionProperty;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\Antlers\ParserTestCase;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;

class HtmlContextTransferTest extends ParserTestCase
{
    use InteractsWithRuntimeConfigurations;

    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
        RuntimeParser::clearRenderNodeCache();
        HtmlInstrumentation::flushAnnotations();
        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'comments' => true, 'prefix' => 'antlers', 'cache' => false]]);
    }

    public function tearDown(): void
    {
        HtmlInstrumentation::transferContexts(true);

        parent::tearDown();
    }

    public function test_freshly_parsed_nodes_adopt_the_analysis_contexts()
    {
        $captured = $this->captureInstrumentedText();
        $parser = app(ParserContract::class);
        $template = '<div><p>{{ title }}</p><script>{{ js }}</script><table>{{ rows }}<tr><td>{{ cell }}</td></tr>{{ /rows }}</table></div>';

        $rendered = (string) $parser->parse($template, ['title' => 'Hello', 'js' => '1', 'rows' => [['cell' => 'c']]]);

        $this->assertStringContainsString('<p><!-- antlers:start ', $rendered);
        $this->assertStringContainsString('<script>1</script>', $rendered);

        $documentParser = $this->documentParser($parser);
        $contexts = HtmlInstrumentation::annotationsFor($captured());
        $nodes = $this->antlersNodes($documentParser);

        $this->assertTrue($this->scanned($documentParser));
        $this->assertCount(5, $contexts);
        $this->assertCount(5, $nodes);

        foreach ($nodes as $index => $node) {
            $this->assertSame($contexts[$index], $node->htmlContext);
        }

        $this->assertSame(['p', 'script', 'div', 'td', 'div'], array_map(fn ($node) => $node->htmlContext->elementName, $nodes));
        $this->assertSame([true, false, false, true, false], array_map(fn ($node) => $node->htmlContext()->isSafeForHtmlComments(), $nodes));
        $this->assertSame(HtmlContext::KIND_RAW_TEXT, $nodes[1]->htmlContext->kind);
        $this->assertTrue($nodes[2]->htmlContext->fosterParented);
    }

    public function test_a_preparser_changing_the_text_after_instrumentation_falls_back_to_the_lazy_scan()
    {
        Runtime::sync()->preparse(fn ($text) => $text.'<!-- footer -->');
        $parser = app(ParserContract::class);

        $rendered = (string) $parser->parse('<p>{{ title }}</p><script>{{ js }}</script>', ['title' => 'Hello', 'js' => '1']);

        $this->assertStringEndsWith('<!-- footer -->', $rendered);
        $this->assertStringContainsString('<p><!-- antlers:start ', $rendered);

        $documentParser = $this->documentParser($parser);
        $nodes = $this->antlersNodes($documentParser);

        $this->assertFalse($this->scanned($documentParser));
        $this->assertSame([null, null], array_map(fn ($node) => $node->htmlContext, $nodes));
        $this->assertSame('p', $nodes[0]->htmlContext()->elementName);
        $this->assertSame(HtmlContext::KIND_RAW_TEXT, $nodes[1]->htmlContext()->kind);
        $this->assertTrue($this->scanned($documentParser));
    }

    public function test_php_templates_fall_back_unless_php_is_allowed()
    {
        $captured = $this->captureInstrumentedText();
        $parser = app(ParserContract::class);
        $template = '<p><?php $b = "{{ x }}"; ?></p><span>{{ y }}</span>';
        $data = ['x' => 'X', 'y' => 'Y'];

        $rendered = (string) $parser->parse($template, $data);

        $this->assertStringContainsString('&lt;?php $b = "X"; ?>', $rendered);
        $this->assertNotNull(HtmlInstrumentation::annotationsFor($captured()));
        $this->assertFalse($this->scanned($this->documentParser($parser)));

        $parser->allowPhp(true);
        RuntimeParser::clearRenderNodeCache();

        try {
            $rendered = (string) $parser->parse($template, $data);
        } finally {
            $parser->allowPhp(false);
        }

        $documentParser = $this->documentParser($parser);
        $nodes = $this->antlersNodes($documentParser);

        $this->assertStringStartsWith('<p></p><span><!-- antlers:start ', $rendered);
        $this->assertTrue($this->scanned($documentParser));
        $this->assertSame(HtmlContext::KIND_PHP, $nodes[0]->htmlContext->kind);
        $this->assertSame('span', $nodes[1]->htmlContext->elementName);
        $this->assertSame(HtmlInstrumentation::annotationsFor($captured())[0], $nodes[0]->htmlContext);
    }

    public function test_eager_annotation_keeps_the_parsers_own_scan()
    {
        $captured = $this->captureInstrumentedText();
        $parser = app(ParserContract::class);
        $this->runtimeConfiguration($parser)->annotateHtmlContext = true;

        $rendered = (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']);

        $this->assertStringContainsString('<p><!-- antlers:start ', $rendered);

        $documentParser = $this->documentParser($parser);
        $nodes = $this->antlersNodes($documentParser);
        $contexts = HtmlInstrumentation::annotationsFor($captured());

        $this->assertTrue($this->scanned($documentParser));
        $this->assertCount(1, $contexts);
        $this->assertInstanceOf(HtmlContext::class, $nodes[0]->htmlContext);
        $this->assertNotSame($contexts[0], $nodes[0]->htmlContext);
        $this->assertSame($contexts[0]->toArray(), $nodes[0]->htmlContext->toArray());
    }

    public function test_multiple_instrumenters_transfer_the_first_instrumenters_contexts()
    {
        config(['statamic.antlers.instrumentation.enabled' => false]);
        $parser = app(ParserContract::class);
        Runtime::register(HtmlInstrumentation::make()->comments('one')->componentPrefixes(['ui-']))
            ->register(HtmlInstrumentation::make()->comments('two'));
        $captured = $this->captureInstrumentedText();

        $rendered = (string) $parser->parse('<ui-box>{{ title }}</ui-box><p>{{ subtitle }}</p>', ['title' => 'Hello', 'subtitle' => 'World']);

        $this->assertStringStartsWith('<ui-box><!-- two:start ', $rendered);
        $this->assertStringNotContainsString('one:start', substr($rendered, 0, strpos($rendered, '<p>')));
        $this->assertStringContainsString('<p><!-- one:start ', $rendered);

        $documentParser = $this->documentParser($parser);
        $nodes = $this->antlersNodes($documentParser);
        $contexts = HtmlInstrumentation::annotationsFor($captured());

        $this->assertTrue($this->scanned($documentParser));
        $this->assertSame($contexts[0], $nodes[0]->htmlContext);
        $this->assertSame([HtmlContext::DYNAMIC_ELEMENT], $nodes[0]->htmlContext->elementStack);
        $this->assertSame(['p'], $nodes[1]->htmlContext->elementStack);
    }

    public function test_the_lazy_scan_honours_the_active_instrumenters_component_prefixes()
    {
        config(['statamic.antlers.instrumentation.componentPrefixes' => ['ui-']]);
        HtmlInstrumentation::transferContexts(false);
        $parser = app(ParserContract::class);

        $parser->parse('<ui-box>{{ title }}</ui-box><p>{{ subtitle }}</p>', ['title' => 'Hello', 'subtitle' => 'World']);

        $documentParser = $this->documentParser($parser);
        $nodes = $this->antlersNodes($documentParser);

        $this->assertFalse($this->scanned($documentParser));
        $this->assertSame([HtmlContext::DYNAMIC_ELEMENT], $nodes[0]->htmlContext()->elementStack);
        $this->assertFalse($nodes[0]->htmlContext()->isSafeForHtmlComments());
        $this->assertTrue($nodes[1]->htmlContext()->isSafeForHtmlComments());

        config(['statamic.antlers.instrumentation.enabled' => false]);
        RuntimeParser::clearRenderNodeCache();
        $parser->parse('<ui-box>{{ title }}</ui-box>', ['title' => 'Hello']);

        $this->assertSame(['ui-box'], $this->antlersNodes($this->documentParser($parser))[0]->htmlContext()->elementStack);
    }

    public function test_adopting_contexts_requires_matching_counts_and_an_unscanned_parser()
    {
        $parser = new DocumentParser;
        $parser->parse('<p>{{ a }}</p>{{ b }}');
        $nodes = $this->antlersNodes($parser);
        $contexts = [new HtmlContext(HtmlContext::KIND_ELEMENT_CONTENT, 'p', null, ['p']), new HtmlContext(HtmlContext::KIND_ELEMENT_CONTENT)];

        $this->assertFalse($parser->adoptHtmlContexts([$contexts[0]]));
        $this->assertSame([null, null], [$nodes[0]->htmlContext, $nodes[1]->htmlContext]);
        $this->assertFalse($this->scanned($parser));

        $this->assertTrue($parser->adoptHtmlContexts($contexts));
        $this->assertSame($contexts[0], $nodes[0]->htmlContext);
        $this->assertSame($contexts[1], $nodes[1]->htmlContext);
        $this->assertTrue($this->scanned($parser));
        $this->assertFalse($parser->adoptHtmlContexts($contexts));

        $parser->parse('<p>{{ a }}</p>');
        $parser->scanHtmlContext();

        $this->assertFalse($parser->adoptHtmlContexts([$contexts[0]]));
    }

    private function captureInstrumentedText(): \Closure
    {
        $captured = null;

        Runtime::sync()->preparse(function ($text) use (&$captured) {
            $captured = $text;

            return $text;
        });

        return function () use (&$captured) {
            return $captured;
        };
    }

    private function documentParser(RuntimeParser $parser): DocumentParser
    {
        return (new ReflectionProperty(RuntimeParser::class, 'documentParser'))->getValue($parser);
    }

    /** @return AntlersNode[] */
    private function antlersNodes(DocumentParser $parser): array
    {
        return array_values(array_filter($parser->getNodes(), fn ($node) => $node instanceof AntlersNode));
    }

    private function scanned(DocumentParser $parser): bool
    {
        return (new ReflectionProperty(DocumentParser::class, 'htmlContextScanned'))->getValue($parser);
    }
}
