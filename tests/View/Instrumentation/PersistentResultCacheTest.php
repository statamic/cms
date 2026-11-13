<?php

namespace Tests\View\Instrumentation;

use Illuminate\Cache\ArrayStore;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\Facades\Cascade;
use Statamic\View\Antlers\Language\Analyzers\Html\Document;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Parser\ComponentCompiler;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\Antlers\TemplateAnalyzer;
use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\Antlers\ParserTestCase;

class PersistentResultCacheTest extends ParserTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
        config(['cache.stores.probe' => ['driver' => 'array', 'serialize' => true]]);
        RuntimeParser::clearRenderNodeCache();
        $this->resetProcessState();
    }

    public function tearDown(): void
    {
        $this->resetProcessState();

        parent::tearDown();
    }

    public function test_config_built_instrumenters_persist_results_and_contexts_across_processes()
    {
        $config = ['comments' => true, 'prefix' => 'antlers', 'attributes' => ['data-antlers'], 'cache' => 'probe'];
        $template = '<div><p>{{ title }}</p><script>{{ js }}</script><table>{{ rows }}<tr><td>{{ cell }}</td></tr>{{ /rows }}</table></div>';

        $instrumenter = HtmlInstrumentation::fromConfig($config);
        $first = $instrumenter->instrument($template);
        $contexts = HtmlInstrumentation::annotationsFor($first);
        $key = $this->storeKeyFor($instrumenter, $template);

        $this->assertStringContainsString('<!-- antlers:start ', $first);
        $this->assertCount(5, $contexts);
        $this->assertCount(1, $this->storedEntries());
        $this->assertNull($instrumenter->lastCacheStoreException());

        $stored = Cache::store('probe')->get($key);

        $this->assertSame($first, $stored[0]);
        $this->assertCount(5, $stored[2]);
        $this->assertLessThan(5, count($stored[1]));

        $this->resetProcessState();
        $this->assertNull(HtmlInstrumentation::annotationsFor($first));

        $fresh = HtmlInstrumentation::fromConfig($config);

        $this->assertSame($first, $fresh->instrument($template));

        $restored = HtmlInstrumentation::annotationsFor($first);

        $this->assertSame(
            array_map(fn ($context) => $context?->toArray(), $contexts),
            array_map(fn ($context) => $context?->toArray(), $restored)
        );
        $this->assertSame([true, false, false], [
            $restored[0]->isSafeForHtmlComments(),
            $restored[1]->isSafeForHtmlComments(),
            $restored[2]->isSafeForHtmlComments(),
        ]);

        Cache::store('probe')->put($key, ['tampered', $stored[1], $stored[2]], 60);
        $this->resetProcessState();

        $this->assertSame('tampered', HtmlInstrumentation::fromConfig($config)->instrument($template));
        $this->assertCount(5, HtmlInstrumentation::annotationsFor('tampered'));
    }

    public function test_ad_hoc_instrumenters_skip_callbacks_and_disabled_caches_leave_the_store_alone()
    {
        $template = '<p>{{ title }}</p>';
        $config = ['comments' => true, 'prefix' => 'antlers', 'cache' => 'probe'];

        HtmlInstrumentation::make()->comments()->instrument($template);
        $this->assertCount(0, $this->storedEntries());

        HtmlInstrumentation::fromConfig(['comments' => true, 'prefix' => 'antlers', 'cache' => false])->instrument($template);
        $this->assertCount(0, $this->storedEntries());

        $reporting = HtmlInstrumentation::fromConfig($config)->onSkip(fn () => null);
        $reporting->instrument('<title>{{ title }}</title>');
        $this->assertCount(0, $this->storedEntries());

        HtmlInstrumentation::instrumentTogether($template, [HtmlInstrumentation::fromConfig($config), HtmlInstrumentation::make()->comments('other')]);
        $this->assertCount(0, $this->storedEntries());

        $together = HtmlInstrumentation::instrumentTogether($template, [
            HtmlInstrumentation::fromConfig($config),
            HtmlInstrumentation::fromConfig(['comments' => true, 'prefix' => 'other', 'cache' => 'probe']),
        ]);

        $this->assertCount(1, $this->storedEntries());
        $this->assertCount(1, HtmlInstrumentation::annotationsFor($together));
        $this->assertSame(1, substr_count($together, '<!-- antlers:start '));
        $this->assertSame(1, substr_count($together, '<!-- other:start '));
    }

    public function test_a_missing_cache_store_degrades_to_the_in_process_memo()
    {
        $template = '<p>{{ title }}</p>';
        $instrumenter = HtmlInstrumentation::fromConfig(['comments' => true, 'prefix' => 'antlers', 'cache' => 'missing']);

        $first = $instrumenter->instrument($template);

        $this->assertStringContainsString('<!-- antlers:start ', $first);
        $this->assertInstanceOf(InvalidArgumentException::class, $instrumenter->lastCacheStoreException());
        $this->assertCount(1, HtmlInstrumentation::annotationsFor($first));

        $memo = new ReflectionProperty(HtmlInstrumentation::class, 'results');
        $memo->setValue(null, array_map(fn ($entry) => ['memoized', $entry[1], $entry[2]], $memo->getValue()));

        $this->assertSame('memoized', $instrumenter->instrument($template));
    }

    public function test_malformed_store_entries_are_recomputed_and_replaced()
    {
        $config = ['comments' => true, 'prefix' => 'antlers', 'cache' => 'probe'];
        $template = '<p>{{ title }}</p>';
        $instrumenter = HtmlInstrumentation::fromConfig($config);
        $key = $this->storeKeyFor($instrumenter, $template);

        $malformedEntries = [
            'garbage',
            [42],
            ['text', 'stacks', 'packed'],
            ['text', [], [[1, 2]]],
            ['text', [['p']], [['element-content', 'p', null, 7, null, 0]]],
            ['text', [['p']], [['element-content', 'p', null, 0, null]]],
            ['text', [['p']], [['kind' => 'element-content', 'element' => 'p', 'attribute' => null, 'stack' => 0, 'offset' => null, 'flags' => 0]]],
            ['text', [['p']], [[7, 'p', null, 0, null, 0]]],
            ['text', [['p']], [['element-content', 'p', null, 0, null, '0']]],
            ['text', [[1]], [['element-content', 'p', null, 0, null, 0]]],
        ];

        foreach ($malformedEntries as $malformed) {
            Cache::store('probe')->put($key, $malformed, 60);
            $this->resetProcessState();

            $result = HtmlInstrumentation::fromConfig($config)->instrument($template);

            $this->assertStringContainsString('<!-- antlers:start ', $result, json_encode($malformed));
            $this->assertSame($result, Cache::store('probe')->get($key)[0]);
            $this->assertCount(1, HtmlInstrumentation::annotationsFor($result));
        }

        Cache::store('probe')->put($key, ['text', null, null], 60);
        $this->resetProcessState();

        $this->assertSame('text', HtmlInstrumentation::fromConfig($config)->instrument($template));
        $this->assertNull(HtmlInstrumentation::annotationsFor('text'));
    }

    public function test_the_configured_instrumenter_uses_the_store_named_in_the_config()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'comments' => true, 'prefix' => 'antlers', 'cache' => 'probe']]);
        Cascade::set('views', ['pages/home.antlers.html' => [], 'pages/about.antlers.html' => []]);

        $rendered = (string) app(ParserContract::class)->parseView('pages/home.antlers.html', '<p>{{ title }}</p>', ['title' => 'Hello']);

        $this->assertStringContainsString('<!-- antlers:start ', $rendered);
        $this->assertCount(1, $this->storedEntries());

        $rendered = (string) app(ParserContract::class)->parse('<p>{{ title }} #1</p>', ['title' => 'Hello'], true);

        $this->assertStringContainsString('<!-- antlers:start ', $rendered);
        $this->assertCount(1, $this->storedEntries());

        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'comments' => true, 'prefix' => 'antlers', 'cache' => false]]);
        app(ParserContract::class)->parseView('pages/about.antlers.html', '<p>{{ subtitle }}</p>', ['subtitle' => 'Hello']);

        $this->assertCount(1, $this->storedEntries());
    }

    public function test_store_hits_hand_the_runtime_the_same_contexts_as_a_fresh_analysis()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'comments' => true, 'prefix' => 'antlers', 'cache' => 'probe']]);
        $template = '<div><p>{{ title }}</p><script>{{ js }}</script>{{ if show }}<b>{{ title }}</b>{{ /if }}</div>';
        $data = ['title' => 'Hello', 'js' => '1', 'show' => true];
        $parser = app(ParserContract::class);

        $first = (string) $parser->parse($template, $data);
        $analysed = $this->contextsOf($parser);

        $this->resetProcessState();
        RuntimeParser::clearRenderNodeCache();

        $second = (string) $parser->parse($template, $data);
        $restored = $this->contextsOf($parser);

        $this->assertSame($first, $second);
        $this->assertCount(5, $analysed);
        $this->assertSame($analysed, $restored);
        $this->assertSame(['element-content', 'raw-text', 'element-content', 'element-content', 'element-content'], array_column($restored, 'kind'));
    }

    public function test_development_builds_stamp_every_directory_the_analysis_reads()
    {
        $directories = array_map(
            'realpath',
            (new ReflectionMethod(HtmlInstrumentation::class, 'analysisSourceDirectories'))->invoke(null)
        );

        $this->assertNotContains(false, $directories);

        foreach ([
            HtmlInstrumentation::class,
            TemplateAnalyzer::class,
            Document::class,
            DocumentParser::class,
            ComponentCompiler::class,
            AntlersNode::class,
            CharacterOffsets::class,
        ] as $class) {
            $this->assertContains(dirname((new ReflectionClass($class))->getFileName()), $directories, $class);
        }
    }

    private function contextsOf(RuntimeParser $parser): array
    {
        $documentParser = (new ReflectionProperty(RuntimeParser::class, 'documentParser'))->getValue($parser);
        $contexts = [];

        foreach ($documentParser->getNodes() as $node) {
            if ($node instanceof AntlersNode) {
                $this->assertInstanceOf(HtmlContext::class, $node->htmlContext);
                $contexts[] = ['kind' => $node->htmlContext->kind, 'stack' => $node->htmlContext->elementStack, 'safe' => $node->htmlContext->isSafeForHtmlComments()];
            }
        }

        return $contexts;
    }

    private function storeKeyFor(HtmlInstrumentation $instrumenter, string $template): string
    {
        $resultKey = (new ReflectionMethod($instrumenter, 'resultCacheKey'))->invoke($instrumenter, $template, GlobalRuntimeState::$currentExecutionFile, true);

        return (new ReflectionMethod($instrumenter, 'cacheStoreKey'))->invoke($instrumenter, $resultKey);
    }

    private function storedEntries(): array
    {
        return (new ReflectionProperty(ArrayStore::class, 'storage'))->getValue(Cache::store('probe')->getStore());
    }

    private function resetProcessState(): void
    {
        (new ReflectionProperty(HtmlInstrumentation::class, 'results'))->setValue(null, []);
        HtmlInstrumentation::flushAnnotations();
    }
}
